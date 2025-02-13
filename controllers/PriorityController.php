<?php
require_once 'models/Priority.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class PriorityController
{
    private $priorityModel;

    public function __construct()
    {
        $this->priorityModel = new Priority();
    }

    public function getPriorities()
    {
        echo json_encode(["status" => "success", "data" => $this->priorityModel->getAllPriorities()]);
    }

    public function createPriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->priorityModel->createPriority($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Priority created"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create Priority"]);
        }
    }

    public function updatePriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->priorityModel->updatePriority($data)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Priority updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update Priority"]);
        }
    }

    public function deletePriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->priorityModel->deletePriority($data['id'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Priority deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete Priority"]);
        }
    }
}
?>