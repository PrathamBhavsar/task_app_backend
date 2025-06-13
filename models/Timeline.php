<?php
class Timeline
{
    private $conn;
    private $table = 'task_timelines';
    private $id = 'timeline_id';

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

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId)
    {
        $query = "
        SELECT *
        FROM task_timelines
        WHERE task_id = :task_id
                ORDER BY created_at ASC

    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (task_id, status_id, user_id) VALUES (:task_id, :status_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':status_id', $data['status_id']);
        $stmt->bindParam(':user_id', $data['user_id']);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();
            return $this->getById($id);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET task_id = :task_id, status_id = :status_id, user_id = :user_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':status_id', $data['status_id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->getById($id);
        }

        return false;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
