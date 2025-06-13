<?php
require_once 'config/database.php';

$db = (new Database())->getConnection();

if ($db) {
    echo "Database connected successfully.";
} else {
    echo "Failed to connect.";
}
