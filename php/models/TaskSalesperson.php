<?php
require_once 'config/database.php';

class TaskSalesperson
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllTaskSalespersons()
    {
        try {
            $query = "SELECT HEX(user_id) AS user_id, HEX(task_id) AS task_id, created_at FROM task_salespersons";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $taskSalespersons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$taskSalespersons) {
                return [];
            }

            return $taskSalespersons;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw new Exception("Database error while fetching users");
        }
    }
}
?>