<?php
class User
{
    private $conn;
    private $table = 'users';
    private $id = 'user_id';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function emailExists($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return false;
    }

    public function register($data)
    {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return ['error' => 'Email already registered'];
        }

        $query = "INSERT INTO {$this->table} 
        (name, email, password, contact_no, user_type, profile_bg_color, address)
        VALUES (:name, :email, :password, :contact_no, :user_type, :profile_bg_color, :address)";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindParam(':contact_no', $data['contact_no']);
        $stmt->bindParam(':user_type', $data['user_type']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':profile_bg_color', $data['profile_bg_color']);

        if ($stmt->execute()) {
            $lastId = $this->conn->lastInsertId();
            return $this->getById($lastId);
        }

        return false;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT  
        user_id,
        created_at,
        name, 
        email, 
        contact_no, 
        address, 
        user_type, 
        profile_bg_color  FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT  
        user_id,
        created_at,
        name, 
        email, 
        contact_no, 
        address, 
        user_type, 
        profile_bg_color  FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, email) VALUES (:name, :email)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET name = :name, email = :email, contact_no = :contact_no, user_type = :user_type, profile_bg_color = :profile_bg_color, address = :address
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':contact_no', $data['contact_no']);
        $stmt->bindParam(':user_type', $data['user_type']);
        $stmt->bindParam(':profile_bg_color', $data['profile_bg_color']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->getById($id);
        }

        return false;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
