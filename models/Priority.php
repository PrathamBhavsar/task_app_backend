<?php
require_once 'config/database.php';

class Priority
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllPriorities()
    {
        $query = "SELECT name, color, created_at FROM task_priority";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPriority($data)
    {
        $query = "INSERT INTO task_priority (name, color, created_at)
                  VALUES (?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['name'], $data['color']]);
        return $stmt->rowCount();
    }

    public function updatePriority($data)
    {
        $query = "UPDATE task_priority SET name=?, color=? WHERE name=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['new_name'], $data['color'], $data['old_name']]);
        return $stmt->rowCount();
    }

    public function deletePriority($name)
    {
        $query = "DELETE FROM task_priority WHERE name=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$name]);
        return $stmt->rowCount();
    }
}
?>
