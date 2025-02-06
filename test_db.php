<?php
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();
if ($conn) {
    echo "Database connected successfully!";
} else {
    echo "Database connection failed!";
}
?>
