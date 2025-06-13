<?php
require_once __DIR__ . '/../helpers/response.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'ds');
define('DB_USER', 'root');
define('DB_PASS', 'Nautilus@610#');

class Database
{
    private $conn;

    public function getConnection()
    {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            sendError("Database connection failed.", 500);
        }
    }
}
