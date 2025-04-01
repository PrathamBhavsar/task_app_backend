<?php
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';

class ClientController
{
    private $clientModel;

    public function __construct()
    {
        $this->clientModel = new Client();
    }

    /**
     * Get all clients
     */
    public function getClients()
    {
        try {
            $clients = $this->clientModel->getAllClients();

            if (!$clients) {
                sendError("No clients found", 404);
            }

            sendResponse("Clients retrieved successfully", $clients);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch clients", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

    /**
     * Create a new client
     */
    public function createClient()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'], $data['address'], $data['contact_no'])) {
            sendError("Missing required fields", 400);
        }

        try {
            if ($this->clientModel->createClient($data)) {
                sendResponse("Client created successfully", [], 201);
            } else {
                sendError("Failed to create client", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create client", 500);
        }
    }

    /**
     * Update client details
     */
    public function updateClient()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'], $data['address'], $data['contact_no'])) {
            sendError("Missing required fields", 400);
        }

        $client = $this->clientModel->findById($data['id']);
        if (!$client) {
            sendError("Client not found", 404);
        }

        try {
            if ($this->clientModel->updateClientById($data['id'], $data)) {
                sendResponse("Client updated successfully");
            } else {
                sendError("No changes made or failed to update client", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update client", 500);
        }
    }

    /**
     * Delete a client
     */
    public function deleteClient()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            sendError("Client ID is required", 400);
        }

        $client = $this->clientModel->findById($data['id']);
        if (!$client) {
            sendError("Client not found", 404);
        }

        try {
            if ($this->clientModel->deleteClientById($data['id'])) {
                sendResponse("Client deleted successfully");
            } else {
                sendError("Failed to delete client", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete client", 500);
        }
    }
}
?>
