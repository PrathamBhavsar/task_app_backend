<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

public function emailExists($email) {
    $query = "SELECT * FROM {$this->table} WHERE email = :email";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function login($email, $password) {
    $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
        unset($user['password']); // Don't expose password
        return $user;
    }

    return false;
}



public function register($data) {
    // Check if email already exists
    if ($this->emailExists($data['email'])) {
        return ['error' => 'Email already registered'];
    }

    $query = "INSERT INTO {$this->table} 
        (name, email, password, contact_no, user_type, profile_bg_color)
        VALUES (:name, :email, :password, :contact_no, :user_type, :profile_bg_color)";
    
    $stmt = $this->conn->prepare($query);

    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindParam(':contact_no', $data['contact_no']);
    $stmt->bindParam(':user_type', $data['user_type']);
    $stmt->bindParam(':profile_bg_color', $data['profile_bg_color']);

    if ($stmt->execute()) {
        $lastId = $this->conn->lastInsertId();
        return $this->getById($lastId); 
    }

    return false;
}

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, email) VALUES (:name, :email)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        return $stmt->execute();
    }
}
?>
