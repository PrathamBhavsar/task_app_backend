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
            $query = "SELECT HEX(id) AS id, name, color, created_at FROM task_priority";
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

            $query = "INSERT INTO task_priority (id, name, color, created_at)
                      VALUES (UNHEX(?), ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            $id = bin2hex(random_bytes(16));

            if ($stmt->execute([$id, $data['name'], $data['color']])) {
                return ["id" => $id];
            } else {
                throw new Exception("Failed to create priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (createPriority): " . $e->getMessage());
            throw new Exception("Database error while creating priority");
        }
    }

    public function updatePriorityById($id, $data)
    {
        try {
            if (!isset($data['name'], $data['color'])) {
                throw new Exception("Missing required fields");
            }

            $priority = $this->findById($id);
            if (!$priority) {
                throw new Exception("Priority not found");
            }

            $query = "UPDATE task_priority SET name=?, color=? WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$data['name'], $data['color'], $id])) {
                return true;
            } else {
                throw new Exception("No changes made or failed to update priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (updatePriorityById): " . $e->getMessage());
            throw new Exception("Database error while updating priority");
        }
    }

    public function deletePriorityById($id)
    {
        try {
            $priority = $this->findById($id);
            if (!$priority) {
                throw new Exception("Priority not found");
            }

            $query = "DELETE FROM task_priority WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$id])) {
                return true;
            } else {
                throw new Exception("Failed to delete priority");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (deletePriorityById): " . $e->getMessage());
            throw new Exception("Database error while deleting priority");
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT HEX(id) AS id, name, color FROM task_priority WHERE id = UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (findById): " . $e->getMessage());
            throw new Exception("Database error while fetching priority details");
        }
    }
}
?>
