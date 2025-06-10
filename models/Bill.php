<?php
class Bill {
    private $conn;
    private $table = 'bills';
    private $id = 'bill_id';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId) {
        $query = "
            SELECT b.*
            FROM bills b
            JOIN tasks t ON b.bill_id = t.bill_id
            WHERE t.task_id = :task_id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
            (due_date, subtotal, tax, total, additional_notes, status) 
            VALUES (:due_date, :subtotal, :tax, :total, :additional_notes, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':additional_notes', $data['additional_notes']);
        $status = $data['status'] ?? 'Pending';
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $billId = $this->conn->lastInsertId();

            if (!empty($data['task_id'])) {
                $update = $this->conn->prepare("UPDATE tasks SET bill_id = :bill_id WHERE task_id = :task_id");
                $update->bindParam(':bill_id', $billId, PDO::PARAM_INT);
                $update->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);
                $update->execute();
            }

            return $this->getById($billId);
        }

        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE {$this->table} 
            SET due_date = :due_date,
                subtotal = :subtotal,
                tax = :tax,
                total = :total,
                additional_notes = :additional_notes,
                status = :status
            WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':additional_notes', $data['additional_notes']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if (!empty($data['task_id'])) {
                $update = $this->conn->prepare("UPDATE tasks SET bill_id = :bill_id WHERE task_id = :task_id");
                $update->bindParam(':bill_id', $id, PDO::PARAM_INT);
                $update->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);
                $update->execute();
            }

            return $this->getById($id);
        }

        return false;
    }

    public function delete($id) {
        // Unlink the bill from any task that references it
        $unlink = $this->conn->prepare("UPDATE tasks SET bill_id = NULL WHERE bill_id = :bill_id");
        $unlink->bindParam(':bill_id', $id, PDO::PARAM_INT);
        $unlink->execute();

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
