<?php
class Service
{
    private $conn;
    private $taskServiceTable = 'task_services';
    private $serviceMasterTable = 'service_master';
    private $taskServiceId = 'task_service_id';
    private $bill;

    public function __construct($db, $bill)
    {
        $this->conn = $db;
        $this->bill = $bill;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT task_service_id FROM {$this->taskServiceTable}");
        $stmt->execute();

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $results = [];
        foreach ($ids as $id) {
            $detailed = $this->getDetailedById($id);
            if ($detailed) $results[] = $detailed;
        }

        return $results;
    }


    public function getDetailedById($id)
    {
        $query = "
        SELECT 
            ts.task_service_id,
            ts.task_id,
            ts.quantity,
            ts.unit_price,
            ts.total_amount,
            sm.service_master_id,
            sm.name AS service_name,
            sm.default_rate
        FROM {$this->taskServiceTable} ts
        JOIN {$this->serviceMasterTable} sm ON ts.service_master_id = sm.service_master_id
        WHERE ts.task_service_id = :id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'task_service_id' => (int)$row['task_service_id'],
            'task_id' => (int)$row['task_id'],
            'service_master' => [
                'service_master_id' => (int)$row['service_master_id'],
                'name' => $row['service_name'],
                'default_rate' => (float)$row['default_rate'],
            ],
            'quantity' => (int)$row['quantity'],
            'unit_price' => (float)$row['unit_price'],
            'total_amount' => (float)$row['total_amount']
        ];
    }


    public function getAllByTaskId($taskId)
    {
        $query = "SELECT task_service_id FROM {$this->taskServiceTable} WHERE task_id = :task_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $results = [];
        foreach ($ids as $id) {
            $detailed = $this->getDetailedById($id);
            if ($detailed) $results[] = $detailed;
        }

        return $results;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->taskServiceTable} WHERE {$this->taskServiceId} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $this->getDetailedById($id);
    }


    public function create($data)
    {
        $query = "INSERT INTO {$this->taskServiceTable} (task_id, service_master_id, quantity, unit_price, total_amount)
                  VALUES (:task_id, :service_master_id, :quantity, :unit_price, :total_amount)";

        $stmt = $this->conn->prepare($query);

        $totalAmount = $data['quantity'] * $data['unit_price'];

        $stmt->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);
        $stmt->bindParam(':service_master_id', $data['service_master_id'], PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':unit_price', $data['unit_price']);
        $stmt->bindParam(':total_amount', $totalAmount);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();
            $this->bill->recalculateForTask($data['task_id']);
            return $this->getDetailedById($id);
        }


        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->taskServiceTable}
                  SET task_id = :task_id, service_master_id = :service_master_id, quantity = :quantity, 
                      unit_price = :unit_price, total_amount = :total_amount
                  WHERE {$this->taskServiceId} = :id";

        $stmt = $this->conn->prepare($query);

        $totalAmount = $data['quantity'] * $data['unit_price'];

        $stmt->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);
        $stmt->bindParam(':service_master_id', $data['service_master_id'], PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':unit_price', $data['unit_price']);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->bill->recalculateForTask($data['task_id']); // 👈 Recalculate
            return $this->getDetailedById($id);
        }

        return false;
    }


    public function delete($id)
    {

        $stmt = $this->conn->prepare("SELECT task_id FROM {$this->taskServiceTable} WHERE {$this->taskServiceId} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $taskId = $stmt->fetchColumn();

        if (!$taskId) return false;

        $deleteStmt = $this->conn->prepare("DELETE FROM {$this->taskServiceTable} WHERE {$this->taskServiceId} = :id");
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            $this->bill->recalculateForTask($taskId);
            return true;
        }

        return false;
    }
}
