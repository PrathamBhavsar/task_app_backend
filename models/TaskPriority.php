<?php
class TaskPriority {
    private $conn;
    private $table = 'task_priorities';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE priority_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function create($data) {
    $query = "INSERT INTO task_priorities (name, color) VALUES (:name, :color)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':color', $data['color']);

    if ($stmt->execute()) {
        $id = $this->conn->lastInsertId();
        return $this->getById($id);
    }

    return false;
}


    public function update($id, $data) {
    $query = "UPDATE {$this->table} 
              SET name = :name, color = :color 
              WHERE priority_id = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':color', $data['color']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return $this->getById($id);
    }

    return false;
}

public function delete($id) {
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE priority_id = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}
?>
