<?php
require_once 'models/Status.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class StatusController
{
    private $statusModel;

    public function __construct()
    {
        $this->statusModel = new Status();
    }

    public function getStatuses()
    {
        echo json_encode(["status" => "success", "data" => $this->statusModel->getAllStatuses()]);
    }

    public function createStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->statusModel->createStatus($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Status created"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create Status"]);
        }
    }

    public function updateStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->statusModel->updateStatus($data)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Status updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update Status"]);
        }
    }

    public function deleteStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->statusModel->deleteStatus($data['id'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Status deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete Status"]);
        }
    }
}
?>