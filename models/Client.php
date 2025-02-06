<?php
require_once 'config/database.php';

class Client {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllClients() {
        $query = "SELECT HEX(id) AS id, name, address, contact_no, created_at FROM clients";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createClient($data) {
        $query = "INSERT INTO clients (id, name, address, contact_no, created_at)
                  VALUES (UNHEX(?), ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $id = bin2hex(random_bytes(16));
        $stmt->execute([$id, $data['name'], $data['address'], $data['contact_no']]);
        return $stmt->rowCount();
    }

    public function updateClient($data) {
        $query = "UPDATE clients SET name=?, address=?, contact_no=? WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['name'], $data['address'], $data['contact_no'], $data['id']]);
        return $stmt->rowCount();
    }

    public function deleteClient($id) {
        $query = "DELETE FROM clients WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>
