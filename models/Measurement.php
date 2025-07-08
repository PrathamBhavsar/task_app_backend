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

    public function getQuoteMeasurementsByTaskId($taskId)
    {
        $query = "
        SELECT 
            qm.quote_measurement_id,
            qm.quote_id,
            qm.measurement_id,
            qm.quantity,
            qm.unit_price,
            qm.total_price,
            qm.discount,

            q.task_id,

            m.measurement_id AS m_id,
            m.task_id AS m_task_id,
            m.location AS m_location,
            m.width AS m_width,
            m.height AS m_height,
            m.area AS m_area,
            m.unit AS m_unit,
            m.notes AS m_notes
        FROM quote_measurements qm
        JOIN quotes q ON qm.quote_id = q.quote_id
        JOIN measurements m ON qm.measurement_id = m.measurement_id
        WHERE q.task_id = :task_id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'quote_measurement_id' => $row['quote_measurement_id'],
                'quote_id' => $row['quote_id'],
                'quantity' => (int)$row['quantity'],
                'rate' => (float)$row['unit_price'],
                'total_price' => (float)$row['total_price'],
                'discount' => (float)$row['discount'],
                'task_id' => (int)$row['task_id'],
                'measurement' => [
                    'measurement_id' => (int)$row['m_id'],
                    'task_id' => (int)$row['m_task_id'],
                    'location' => $row['m_location'],
                    'width' => $row['m_width'],
                    'height' => $row['m_height'],
                    'area' => $row['m_area'],
                    'unit' => $row['m_unit'],
                    'notes' => $row['m_notes'],
                ]
            ];
        }

        return sendJson(['quote_measurements' => $result]);
    }


    public function createBulk($measurements)
    {
        if (empty($measurements)) {
            sendError("No measurements provided", 400);
        }

        $this->conn->beginTransaction();

        try {
            $firstMeasurement = $measurements[0];
            $taskId = $firstMeasurement['task_id'] ?? null;

            if (!$taskId) {
                sendError("task_id is required in all measurements", 400);
            }

            foreach ($measurements as $data) {
                if (!isset($data['task_id']) || $data['task_id'] != $taskId) {
                    sendError("All measurements must have the same valid task_id", 400);
                }
                $this->create($data);
            }

            $this->conn->commit();

            $allMeasurements = $this->getAllByTaskId($taskId);
            sendJson([
                "measurements" => $allMeasurements,
                201
            ]);
        } catch (Exception $e) {
            $this->conn->rollBack();
            sendError("Failed to insert measurements: " . $e->getMessage(), 500);
        }
    }




    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (location, width, height, notes, area, unit, task_id) 
              VALUES (:location, :width, :height, :notes, :area, :unit, :task_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':unit', $data['unit']);
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
              SET location = :location, width = :width, height = :height, notes = :notes, area = :area, unit = :unit, task_id = :task_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':unit', $data['unit']);
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
