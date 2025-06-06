<?php
class Client {
    private $conn;
    private $table = 'clients';
    private $id = 'client_id';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function create($data) {
    $query = "INSERT INTO {$this->table} (name, contact_no, email, address) VALUES (:name, :contact_no, :email, :address)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':contact_no', $data['contact_no']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':address', $data['address']);

    if ($stmt->execute()) {
        $id = $this->conn->lastInsertId();
        return $this->getById($id);
    }

    return false;
}

    public function update($id, $data) {
    $query = "UPDATE {$this->table} 
              SET name = :name, contact_no = :contact_no, email = :email, address = :address
              WHERE {$this->id} = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':contact_no', $data['contact_no']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return $this->getById($id);
    }

    return false;
}

public function delete($id) {
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}
?>
