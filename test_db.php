<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo json_encode(["status" => "success", "message" => "Database connected successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
}
?>
