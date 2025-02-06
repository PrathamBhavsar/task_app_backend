<?php
require_once('/home4/drapesho/config/db_config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $conn;

    public function getConnection() {
        global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME; // from config.php

        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die(json_encode(["status" => "error", "message" => "Database Connection Failed: " . $exception->getMessage()]));
        }
        return $this->conn;
    }
}
?>
