<?php
require_once 'config/database.php';

class Status
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllStatuses()
    {
        $query = "SELECT task_order, name, slug, color, category, created_at FROM task_status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createStatus($data)
    {
        $query = "INSERT INTO task_status (name, slug, color, category, created_at)
                  VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['name'], $data['slug'], $data['color'], $data['category']]);
        return $this->conn->lastInsertId();
    }

    public function updateStatus($data)
    {
        $query = "UPDATE task_status SET name=?, slug=?, color=?, category=? WHERE task_order=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['name'], $data['slug'], $data['color'], $data['category'], $data['task_order']]);
        return $stmt->rowCount();
    }

    public function deleteStatus($task_order)
    {
        $query = "DELETE FROM task_status WHERE task_order=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$task_order]);
        return $stmt->rowCount();
    }
}
?>
