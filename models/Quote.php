<?php
class Quote
{
    private $conn;
    private $table = 'quotes';
    private $id = 'quote_id';

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
            FROM quotes b
            JOIN tasks t ON b.quote_id = t.quote_id
            WHERE t.task_id = :task_id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuoteIdByTaskId($taskId)
    {
        $stmt = $this->conn->prepare("SELECT quote_id FROM quotes WHERE task_id = :task_id LIMIT 1");
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['quote_id'] : null;
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
        // 1. Calculate subtotal from task_services
        $queryServices = "SELECT quantity, unit_price FROM task_services WHERE task_id = :task_id";
        $stmtServices = $this->conn->prepare($queryServices);
        $stmtServices->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmtServices->execute();
        $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

        $subtotal = 0;
        foreach ($services as $service) {
            $subtotal += $service['quantity'] * $service['unit_price'];
        }

        // 2. Get quote_id (quote must exist or be created)
        $quoteId = $this->getQuoteIdByTaskId($taskId);

        if (!$quoteId) {
            // create placeholder quote to get quote_id for quote_measurements
            $this->create([
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'notes' => null,
                'task_id' => $taskId
            ]);
            $quoteId = $this->getQuoteIdByTaskId($taskId); // now get the ID again
        }

        // 3. Calculate subtotal from quote_measurements
        $queryMeasurements = "
        SELECT quantity, unit_price 
        FROM quote_measurements 
        WHERE quote_id = :quote_id
    ";
        $stmtMeasurements = $this->conn->prepare($queryMeasurements);
        $stmtMeasurements->bindParam(':quote_id', $quoteId, PDO::PARAM_INT);
        $stmtMeasurements->execute();
        $measurements = $stmtMeasurements->fetchAll(PDO::FETCH_ASSOC);

        foreach ($measurements as $m) {
            $subtotal += $m['quantity'] * $m['unit_price'];
        }

        // 4. Calculate tax (7%)
        $tax = round($subtotal * 0.07, 2);

        // 5. Calculate total
        $total = round($subtotal + $tax, 2);

        // 6. Update the quote
        $updateData = [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'notes' => null,
            'task_id' => $taskId
        ];

        return $this->update($quoteId, $updateData);
    }



    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
            (subtotal, tax, total, notes, task_id) 
            VALUES (:subtotal, :tax, :total, :notes, :task_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);

        if ($stmt->execute()) {
            $quoteId = $this->conn->lastInsertId();

            return $this->getById($quoteId);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
            SET subtotal = :subtotal,
                tax = :tax,
                total = :total,
                notes = :notes,
                task_id = :task_id
            WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax', $data['tax']);
        $stmt->bindParam(':total', $data['total']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $this->getById($id);
        }

        return false;
    }

    public function delete($id)
    {
        // Unlink the quote from any task that references it
        $unlink = $this->conn->prepare("UPDATE tasks SET quote_id = NULL WHERE quote_id = :quote_id");
        $unlink->bindParam(':quote_id', $id, PDO::PARAM_INT);
        $unlink->execute();

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
