<?php
require_once 'config/database.php';

class Client
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllClients()
    {
        try {
            $query = "SELECT HEX(id) AS id, name, address, contact_no, created_at FROM clients";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$clients) {
                return []; // Return empty array if no clients found
            }

            return $clients;
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllClients): " . $e->getMessage());
            throw new Exception("Database error while fetching clients");
        }
    }

    public function createClient($data)
    {
        try {
            if (!isset($data['name'], $data['address'], $data['contact_no'])) {
                throw new Exception("Missing required fields");
            }

            $query = "INSERT INTO clients (id, name, address, contact_no, created_at)
                      VALUES (UNHEX(?), ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            $id = bin2hex(random_bytes(16));

            if ($stmt->execute([$id, $data['name'], $data['address'], $data['contact_no']])) {
                return true;
            } else {
                throw new Exception("Failed to create client");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (createClient): " . $e->getMessage());
            throw new Exception("Database error while creating client");
        }
    }

    public function updateClientById($id, $data)
    {
        try {
            if (!isset($data['name'], $data['address'], $data['contact_no'])) {
                throw new Exception("Missing required fields");
            }

            $client = $this->findById($id);
            if (!$client) {
                throw new Exception("Client not found");
            }

            $query = "UPDATE clients SET name=?, address=?, contact_no=? WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$data['name'], $data['address'], $data['contact_no'], $id])) {
                return true;
            } else {
                throw new Exception("No changes made or failed to update client");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (updateClientById): " . $e->getMessage());
            throw new Exception("Database error while updating client");
        }
    }

    public function deleteClientById($id)
    {
        try {
            $client = $this->findById($id);
            if (!$client) {
                throw new Exception("Client not found");
            }

            $query = "DELETE FROM clients WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$id])) {
                return true;
            } else {
                throw new Exception("Failed to delete client");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (deleteClientById): " . $e->getMessage());
            throw new Exception("Database error while deleting client");
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT HEX(id) AS id, name, address, contact_no FROM clients WHERE id = UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (findById): " . $e->getMessage());
            throw new Exception("Database error while fetching client details");
        }
    }
}
?>
