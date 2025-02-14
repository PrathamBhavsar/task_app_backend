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
        try {
            $query = "SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, HEX(created_by) AS created_by, remarks, status FROM tasks";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $tasks ?: [];
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllTasks): " . $e->getMessage());
            throw new Exception("Database error while fetching tasks");
        }
    }

    public function getTaskById($id)
    {
        try {
            $query = "SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, HEX(created_by) AS created_by, remarks, status FROM tasks WHERE id = UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (getTaskById): " . $e->getMessage());
            throw new Exception("Database error while fetching task details");
        }
    }

    public function createTask($data)
    {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO tasks (id, deal_no, name, created_at, start_date, due_date, priority, created_by, remarks, status)
                      VALUES (UNHEX(?), ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, UNHEX(?), ?, ?)";
            $stmt = $this->conn->prepare($query);
            $taskId = bin2hex(random_bytes(16));

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

            $this->insertTaskRelatedRecords($taskId, $data);
            $this->conn->commit();

            return ["id" => $taskId];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Database Transaction Error (createTask): " . $e->getMessage());
            throw new Exception("Database error while creating task");
        }
    }

    private function insertTaskRelatedRecords($taskId, $data)
    {
        $relations = [
            'clients' => 'task_clients',
            'designers' => 'task_designers',
            'agencies' => 'task_agencies',
            'salespersons' => 'task_salespersons'
        ];

        foreach ($relations as $key => $table) {
            if (!empty($data[$key])) {
                $query = "INSERT INTO $table (task_id, user_id, created_at) VALUES (UNHEX(?), UNHEX(?), CURRENT_TIMESTAMP)";
                $stmt = $this->conn->prepare($query);
                foreach ($data[$key] as $userId) {
                    $stmt->execute([$taskId, $userId]);
                }
            }
        }

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

            $this->deleteTaskRelatedRecords($data['id']);
            $this->insertTaskRelatedRecords($data['id'], $data);

            $this->conn->commit();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Database Transaction Error (updateTask): " . $e->getMessage());
            throw new Exception("Database error while updating task");
        }
    }

    private function deleteTaskRelatedRecords($taskId)
    {
        $tables = ['task_clients', 'task_designers', 'task_agencies', 'task_salespersons', 'task_attachments'];
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
            $this->deleteTaskRelatedRecords($id);
            $query = "DELETE FROM tasks WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $this->conn->commit();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Database Transaction Error (deleteTask): " . $e->getMessage());
            throw new Exception("Database error while deleting task");
        }
    }
}
?>
