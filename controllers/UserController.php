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

    // ✅ Register new user
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
            return;
        }

        if ($this->userModel->findByEmail($data['email'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Email already exists"]);
            return;
        }

        if ($this->userModel->registerUser($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "User registered successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Registration failed"]);
        }
    }

    // ✅ User login
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Email and password are required"]);
            return;
        }

        $user = $this->userModel->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
            return;
        }

        $token = bin2hex(random_bytes(32)); // Simple token (replace with JWT in production)
        echo json_encode(["status" => "success", "message" => "Login successful", "token" => $token]);
    }
}
