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
        try {
            $query = "SELECT name, color, created_at FROM task_priority";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $priorities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $priorities ?: [];
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllPriorities): " . $e->getMessage());
            throw new Exception("Database error while fetching priorities");
        }
    }

    public function createPriority($data)
    {
        try {
            if (!isset($data['name'], $data['color'])) {
                throw new Exception("Missing required fields");
            }

            $query = "INSERT INTO task_priority (name, color, created_at)
                      VALUES (?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$data['name'], $data['color']])) {
                return ["message" => "Priority created successfully"];
            } else {
                throw new Exception("Failed to create priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (createPriority): " . $e->getMessage());
            throw new Exception("Database error while creating priority");
        }
    }



    public function updatePriorityByName($name, $data)
    {
        try {
            if (!isset($data['name'], $data['color'])) {
                throw new Exception("Missing required fields");
            }

            $priority = $this->findByName($name);
            if (!$priority) {
                throw new Exception("Priority not found");
            }

            $query = "UPDATE task_priority SET name=?, color=? WHERE name=?";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$data['name'], $data['color'], $name])) {
                return true;
            } else {
                throw new Exception("No changes made or failed to update priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (updatePriorityByName): " . $e->getMessage());
            throw new Exception("Database error while updating priority");
        }
    }


    public function deletePriorityByName($name)
    {
        try {
            $priority = $this->findByName($name);
            if (!$priority) {
                throw new Exception("Priority not found");
            }

            $query = "DELETE FROM task_priority WHERE name=?";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$name])) {
                return true;
            } else {
                throw new Exception("Failed to delete priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (deletePriorityByName): " . $e->getMessage());
            throw new Exception("Database error while deleting priority");
        }
    }

    public function findByName($name)
    {
        try {
            $query = "SELECT name, color FROM task_priority WHERE name = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$name]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (findByName): " . $e->getMessage());
            throw new Exception("Database error while fetching priority details");
        }
    }
}
?>