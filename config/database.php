<?php
require_once('/home4/drapesho/config/db_config.php');
require_once __DIR__ . '/../errorHandler.php';

class Database
{
    private $conn;

    public function getConnection()
    {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            sendError("Database connection failed. Please try again later.", 500);
        }
    }
}
?>
