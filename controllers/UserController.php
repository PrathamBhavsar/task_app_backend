<?php
require_once 'models/User.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function getUsers()
    {
        echo json_encode(["status" => "success", "data" => $this->userModel->getAllUsers()]);
    }

    public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userModel->createUser($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "User created"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create User"]);
        }
    }

    public function updateUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userModel->updateUser($data)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "User updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update User"]);
        }
    }

    public function deleteUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userModel->deleteUser($data['id'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "User deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete User"]);
        }
    }
}
?>