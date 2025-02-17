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
        try {
            $query = "SELECT HEX(id) AS id, name, email, role, profile_bg_color, created_at FROM users";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$users) {
                return []; // Return empty array if no users found
            }

            return $users;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage()); // Log the exact error
            throw new Exception("Database error while fetching users");
        }
    }


    public function createUser($data)
    {
        $query = "INSERT INTO users (id, name, email, role, profile_bg_color, created_at)
                  VALUES (UNHEX(?), ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $id = bin2hex(random_bytes(16));
        return $stmt->execute([$id, $data['name'], $data['email'], $data['role'], $data['profile_bg_color']]);
    }

    public function updateUserById($id, $data)
    {
        $query = "UPDATE users SET name=?, email=?, role=?, profile_bg_color=? WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$data['name'], $data['email'], $data['role'], $data['profile_bg_color'], $id]);
    }

    public function deleteUserById($id)
    {
        $query = "DELETE FROM users WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function registerUser($data)
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Generate a static token based on email and password hash
        $staticToken = hash('sha256', $data['email'] . $hashedPassword);

        $query = "INSERT INTO users (id, name, email, password, role, profile_bg_color, api_token, created_at)
                  VALUES (UNHEX(?), ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($query);
        $id = bin2hex(random_bytes(16));

        return $stmt->execute([
            $id,
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'],
            $data['profile_bg_color'],
            $staticToken
        ]) ? $staticToken : false;
    }

    public function findByEmail($email)
    {
        $query = "SELECT HEX(id) AS id, name, email, password, role, api_token FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function findById($id)
    {
        $query = "SELECT HEX(id) AS id, name, email, role FROM users WHERE id = UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>