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
        try {
            $query = "SELECT task_order, name, slug, color, category, created_at FROM task_status";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllStatuses): " . $e->getMessage());
            throw new Exception("Database error while fetching statuses");
        }
    }

    public function createStatus($data)
    {
        try {
            if (!isset($data['name'], $data['slug'], $data['color'], $data['category'])) {
                throw new Exception("Missing required fields");
            }

            $query = "INSERT INTO task_status (name, slug, color, category, created_at)
                      VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$data['name'], $data['slug'], $data['color'], $data['category']]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database Query Error (createStatus): " . $e->getMessage());
            throw new Exception("Database error while creating status");
        }
    }

    public function updateStatusById($id, $data)
    {
        try {
            $query = "UPDATE task_status SET name=?, slug=?, color=?, category=? WHERE task_order=?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$data['name'], $data['slug'], $data['color'], $data['category'], $id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Query Error (updateStatusById): " . $e->getMessage());
            throw new Exception("Database error while updating status");
        }
    }

    public function deleteStatusById($id)
    {
        try {
            $query = "DELETE FROM task_status WHERE task_order=?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Query Error (deleteStatusById): " . $e->getMessage());
            throw new Exception("Database error while deleting status");
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT task_order, name, slug, color, category FROM task_status WHERE task_order = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (findById): " . $e->getMessage());
            throw new Exception("Database error while fetching status details");
        }
    }
}
?>
