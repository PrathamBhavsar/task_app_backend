<?php
class Bill
{
    private $conn;
    private $table = 'bills';
    private $id = 'bill_id';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId)
    {
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

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function recalculateForTask($taskId)
    {
        // 1. Get all task_services for this task_id
        $query = "SELECT quantity, unit_price FROM task_services WHERE task_id = :task_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$services || count($services) === 0) {
            return false; // No services, no bill
        }

        // 2. Calculate subtotal
        $subtotal = 0;
        foreach ($services as $service) {
            $subtotal += $service['quantity'] * $service['unit_price'];
        }

        // 3. Calculate tax (7%)
        $tax = round($subtotal * 0.07, 2);

        // 4. Calculate total
        $total = round($subtotal + $tax, 2);

        // 5. Check if a bill already exists for this task
        $checkQuery = "SELECT bill_id FROM bills WHERE task_id = :task_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $checkStmt->execute();
        $existingBill = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingBill) {
            // Update existing bill
            $updateData = [
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'additional_notes' => null,
                'status' => 'Pending',
                'task_id' => $taskId
            ];
            return $this->update($existingBill['bill_id'], $updateData);
        } else {
            // Create new bill
            $insertData = [
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'additional_notes' => null,
                'status' => 'Pending',
                'task_id' => $taskId
            ];
            return $this->create($insertData);
        }
    }


    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
            (due_date, subtotal, tax, total, additional_notes, status, task_id) 
            VALUES (:due_date, :subtotal, :tax, :total, :additional_notes, :status, :task_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':additional_notes', $data['additional_notes']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $status = $data['status'] ?? 'Pending';
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $billId = $this->conn->lastInsertId();

            return $this->getById($billId);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
            SET due_date = :due_date,
                subtotal = :subtotal,
                tax = :tax,
                total = :total,
                additional_notes = :additional_notes,
                task_id = :task_id,
                status = :status
            WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':additional_notes', $data['additional_notes']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $this->getById($id);
        }

        return false;
    }

    public function delete($id)
    {
        // Unlink the bill from any task that references it
        $unlink = $this->conn->prepare("UPDATE tasks SET bill_id = NULL WHERE bill_id = :bill_id");
        $unlink->bindParam(':bill_id', $id, PDO::PARAM_INT);
        $unlink->execute();

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
