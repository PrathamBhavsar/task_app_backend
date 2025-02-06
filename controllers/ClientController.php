<?php
require_once 'models/Client.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ClientController {
    private $clientModel;

    public function __construct() {
        $this->clientModel = new Client();
    }

    public function getClients() {
        echo json_encode(["status" => "success", "data" => $this->clientModel->getAllClients()]);
    }

    public function createClient() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->clientModel->createClient($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Client created"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create Client"]);
        }
    }

    public function updateClient() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->clientModel->updateClient($data)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Client updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update Client"]);
        }
    }

    public function deleteClient() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->clientModel->deleteClient($data['id'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Client deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete Client"]);
        }
    }
}
?>
