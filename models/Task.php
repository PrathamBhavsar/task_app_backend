<?php
require_once 'config/database.php';

class Task
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllTasks()
    {
        $query = "SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, HEX(created_by) AS created_by, remarks, status FROM tasks";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTaskById($id)
    {
        $query = "SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, HEX(created_by) AS created_by, remarks, status FROM tasks WHERE id = UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTask($data)
    {
        try {
            $this->conn->beginTransaction();

            // Create Task
            $query = "INSERT INTO tasks (id, deal_no, name, created_at, start_date, due_date, priority, created_by, remarks, status)
                  VALUES (UNHEX(?), ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, UNHEX(?), ?, ?)";
            $stmt = $this->conn->prepare($query);

            $taskId = bin2hex(random_bytes(16)); // Generate unique ID
            $stmt->execute([
                $taskId,
                $data['deal_no'],
                $data['name'],
                $data['start_date'],
                $data['due_date'],
                $data['priority'],
                $data['created_by'],
                $data['remarks'] ?? '',
                $data['status']
            ]);

            // Insert related records
            $this->insertTaskRelatedRecords($taskId, $data);

            $this->conn->commit();
            return ["id" => $taskId, "rows_affected" => $stmt->rowCount()];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["error" => $e->getMessage()];
        }
    }


    private function insertTaskRelatedRecords($taskId, $data)
    {
        // Insert Task Clients
        if (!empty($data['clients'])) {
            $query = "INSERT INTO task_clients (task_id, client_id, created_at) VALUES (UNHEX(?), UNHEX(?), CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            foreach ($data['clients'] as $clientId) {
                $stmt->execute([$taskId, $clientId]);
            }
        }

        // Insert Task Designers
        if (!empty($data['designers'])) {
            $query = "INSERT INTO task_designers (task_id, designer_id, created_at) VALUES (UNHEX(?), UNHEX(?), CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            foreach ($data['designers'] as $designerId) {
                $stmt->execute([$taskId, $designerId]);
            }
        }

        // Insert Task Agencies
        if (!empty($data['agencies'])) {
            $query = "INSERT INTO task_agencies (task_id, user_id, created_at) VALUES (UNHEX(?), UNHEX(?), CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            foreach ($data['agencies'] as $userId) {
                $stmt->execute([$taskId, $userId]);
            }
        }

        // Insert Task Attachments
        if (!empty($data['attachments'])) {
            $query = "INSERT INTO task_attachments (id, task_id, attachment_url, attachment_name, created_at) 
                  VALUES (UNHEX(?), UNHEX(?), ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            foreach ($data['attachments'] as $attachment) {
                $attachmentId = bin2hex(random_bytes(16));
                $stmt->execute([$attachmentId, $taskId, $attachment['url'], $attachment['name']]);
            }
        }
    }


    public function updateTask($data)
    {
        try {
            $this->conn->beginTransaction();

            // Update Task
            $query = "UPDATE tasks SET deal_no=?, name=?, start_date=?, due_date=?, priority=?, created_by=UNHEX(?), remarks=?, status=? WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['deal_no'],
                $data['name'],
                $data['start_date'],
                $data['due_date'],
                $data['priority'],
                $data['created_by'],
                $data['remarks'] ?? '',
                $data['status'],
                $data['id']
            ]);

            // Delete old related records and insert new ones
            $this->deleteTaskRelatedRecords($data['id']);
            $this->insertTaskRelatedRecords($data['id'], $data);

            $this->conn->commit();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["error" => $e->getMessage()];
        }
    }


    private function deleteTaskRelatedRecords($taskId)
    {
        $tables = ['task_clients', 'task_designers', 'task_agencies', 'task_attachments'];
        foreach ($tables as $table) {
            $query = "DELETE FROM $table WHERE task_id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$taskId]);
        }
    }

    public function deleteTask($id)
    {
        try {
            $this->conn->beginTransaction();

            // Delete related records first
            $this->deleteTaskRelatedRecords($id);

            // Delete Task
            $query = "DELETE FROM tasks WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $this->conn->commit();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["error" => $e->getMessage()];
        }
    }

}
?>