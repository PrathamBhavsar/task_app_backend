<?php
class Measurement
{
    private $conn;
    private $table = 'measurements';
    private $id = 'measurement_id';
    private $quote;

    public function __construct($db, $quote = null)
    {
        $this->conn = $db;
        $this->quote = $quote;
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
        SELECT *
        FROM measurements 
        WHERE task_id = :task_id
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getQuoteIdByTaskId($taskId)
    {
        $stmt = $this->conn->prepare("SELECT quote_id FROM quotes WHERE task_id = :task_id");
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (location, width, height, notes, task_id) 
              VALUES (:location, :width, :height, :notes, :task_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);

        if ($stmt->execute()) {
            $measurementId = $this->conn->lastInsertId();

            $quoteId = $this->getQuoteIdByTaskId($data['task_id']);
            if ($quoteId) {
                $insertQuoteMeasurement = $this->conn->prepare("
                INSERT INTO quote_measurements (quote_id, measurement_id)
                VALUES (:quote_id, :measurement_id)
            ");
                $insertQuoteMeasurement->bindParam(':quote_id', $quoteId, PDO::PARAM_INT);
                $insertQuoteMeasurement->bindParam(':measurement_id', $measurementId, PDO::PARAM_INT);
                $insertQuoteMeasurement->execute();
            }

            $this->quote->recalculateForTask($data['task_id']);
            return $this->getById($measurementId);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET location = :location, width = :width, height = :height, notes = :notes, task_id = :task_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Sync quote_measurements
            $quoteId = $this->getQuoteIdByTaskId($data['task_id']);
            if ($quoteId) {
                // Check if quote_measurement already exists
                $checkStmt = $this->conn->prepare("
                SELECT COUNT(*) FROM quote_measurements 
                WHERE measurement_id = :measurement_id
            ");
                $checkStmt->bindParam(':measurement_id', $id, PDO::PARAM_INT);
                $checkStmt->execute();
                $exists = $checkStmt->fetchColumn();

                if ($exists) {
                    // Update quote_id in case it changed (e.g. new task)
                    $updateQuoteStmt = $this->conn->prepare("
                    UPDATE quote_measurements 
                    SET quote_id = :quote_id 
                    WHERE measurement_id = :measurement_id
                ");
                    $updateQuoteStmt->bindParam(':quote_id', $quoteId, PDO::PARAM_INT);
                    $updateQuoteStmt->bindParam(':measurement_id', $id, PDO::PARAM_INT);
                    $updateQuoteStmt->execute();
                } else {
                    // Insert new quote_measurement
                    $insertStmt = $this->conn->prepare("
                    INSERT INTO quote_measurements (quote_id, measurement_id, quantity, unit_price, total_price)
                    VALUES (:quote_id, :measurement_id, 1, NULL, NULL)
                ");
                    $insertStmt->bindParam(':quote_id', $quoteId, PDO::PARAM_INT);
                    $insertStmt->bindParam(':measurement_id', $id, PDO::PARAM_INT);
                    $insertStmt->execute();
                }
            }

            $this->quote->recalculateForTask($data['task_id']);
            return $this->getById($id);
        }

        return false;
    }


    public function delete($id)
    {

        $stmt = $this->conn->prepare("SELECT task_id FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $taskId = $stmt->fetchColumn();

        if (!$taskId) return false;

        $unlinkQuote = $this->conn->prepare("DELETE FROM quote_measurements WHERE measurement_id = :mid");
        $unlinkQuote->bindParam(':mid', $id, PDO::PARAM_INT);
        $unlinkQuote->execute();

        $unlinkTask = $this->conn->prepare("DELETE FROM measurements WHERE measurement_id = :mid");
        $unlinkTask->bindParam(':mid', $id, PDO::PARAM_INT);
        $unlinkTask->execute();

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $this->quote->recalculateForTask($taskId);
        return $stmt->execute();
    }
}
