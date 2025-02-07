<?php
require_once 'config/database.php';

class User
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllUsers()
    {
        $query = "SELECT HEX(id) AS id, name, email, role, profile_bg_color, created_at FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($data)
    {
        $query = "INSERT INTO users (id, name, email, role, profile_bg_color, created_at)
                  VALUES (UNHEX(?), ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $id = bin2hex(random_bytes(16));
        $stmt->execute([$id, $data['name'], $data['email'], $data['role'], $data['profile_bg_color']]);
        return $stmt->rowCount();
    }

    public function updateUser($data)
    {
        $query = "UPDATE users SET name=?, email=?, role=?, profile_bg_color=? WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['name'], $data['email'], $data['role'], $data['profile_bg_color'], $data['id']]);
        return $stmt->rowCount();
    }

    public function deleteUser($id)
    {
        $query = "DELETE FROM users WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>