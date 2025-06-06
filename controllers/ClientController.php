<?php
require_once __DIR__ . '/../models/Client.php';

class ClientController {
    private $clientModel;

    public function __construct($db) {
        $this->clientModel = new Client($db);
    }

    public function index() {
        $clients = $this->clientModel->getAll();
        sendJson($clients);
    }

    public function show($id) {
        $client = $this->clientModel->getById($id);
        $client ? sendJson($client) : sendError("Client not found", 404);
    }

public function store($data) {
    if (empty($data['name']) || empty($data['contact_no']) || empty($data['email']) || empty($data['address'])) {
        sendError("Name, contact_no, email and address are required", 400);
    }

    $createdClient = $this->clientModel->create($data);

    if ($createdClient) {
        sendJson(["client" => $createdClient]);
    } else {
        sendError("Client creation failed", 400);
    }
}


public function update($id, $data) {
     if (empty($data['name']) || empty($data['contact_no']) || empty($data['email']) || empty($data['address'])) {
        sendError("Name, contact_no, email and address are required", 400);
    }

    $updatedClient = $this->clientModel->update($id, $data);

    if ($updatedClient) {
        sendJson(["client" => $updatedClient]);
    } else {
        sendError("Update failed", 400);
    }
}

public function delete($id) {
    $client = $this->clientModel->getById($id);

    if (!$client) {
        sendError("Client not found", 404);
    }

    $success = $this->clientModel->delete($id);

    if ($success) {
        sendJson(["client" => $client]);
    } else {
        sendError("Delete failed", 400);
    }
}
}
?>
