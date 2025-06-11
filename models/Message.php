<?php
class Message {
    private $conn;
    private $table = 'task_messages';
    private $id = 'message_id';

    public function __construct($db) { 
        $this->conn = $db;
    }

public function getAll() {
    $query = "
        SELECT m.*, u.*
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        ORDER BY m.created_at ASC
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function getAllByTaskId($taskId) {
    $query = "
        SELECT m.*, u.*
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.task_id = :task_id
        ORDER BY m.created_at ASC
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getDetailedMessageById($id) {
    $query = "
        SELECT m.*, u.*
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.{$this->id} = :id
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


public function create($data) {
    $query = "INSERT INTO {$this->table} (task_id, message, user_id) VALUES (:task_id, :message, :user_id)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':task_id', $data['task_id']);
    $stmt->bindParam(':message', $data['message']);
    $stmt->bindParam(':user_id', $data['user_id']);

    if ($stmt->execute()) {
        $id = $this->conn->lastInsertId();
        return $this->getDetailedMessageById($id);
    }

    return false;
}

    public function update($id, $data) {
    $query = "UPDATE {$this->table} 
              SET task_id = :task_id, message = :message, user_id = :user_id
              WHERE {$this->id} = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':task_id', $data['task_id']);
    $stmt->bindParam(':message', $data['message']);
    $stmt->bindParam(':user_id', $data['user_id']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return $this->getDetailedMessageById($id);
    }

    return false;
}

public function delete($id) {
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}
?>
