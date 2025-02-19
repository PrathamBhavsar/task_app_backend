<?php
require_once 'config/database.php';

class TaskAgency
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllTaskAgencies()
    {
        try {
            $query = "SELECT HEX(user_id) AS user_id, HEX(task_id) AS task_id, created_at FROM task_agencies";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $taskAgencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$taskAgencies) {
                return [];
            }

            return $taskAgencies;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw new Exception("Database error while fetching users");
        }
    }
}
?>