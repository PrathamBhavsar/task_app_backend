<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function getUsers()
    {
        try {
            $users = $this->userModel->getAllUsers();

            if (!$users) {
                sendError("No users found", 404);
            }

            sendResponse("Users retrieved successfully", $users);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Log the exact error
            sendError("Database error: Unable to fetch users", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }


    public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name'], $data['email'], $data['role'])) {
            sendError("Missing required fields", 400);
        }

        try {
            if ($this->userModel->createUser($data)) {
                sendResponse("User created successfully", [], 201);
            } else {
                sendError("Failed to create user", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create user", 500);
        }
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
            sendError("All fields are required", 400);
        }

        if ($this->userModel->findByEmail($data['email'])) {
            sendError("Email already exists", 400);
        }

        if ($this->userModel->registerUser($data)) {
            sendResponse("User registered successfully", [], 201);
        } else {
            sendError("Registration failed", 500);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['email'], $data['password'])) {
            sendError("Email and password are required", 400);
        }

        $user = $this->userModel->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            sendError("Invalid credentials", 401);
        }

        $token = bin2hex(random_bytes(32)); // Replace with JWT in production
        sendResponse("Login successful", ["token" => $token]);
    }

    public function updateUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'], $data['name'], $data['email'], $data['role'])) {
            sendError("Missing required fields", 400);
        }

        $user = $this->userModel->findById($data['id']);
        if (!$user) {
            sendError("User not found", 404);
        }

        try {
            if ($this->userModel->updateUserById($data['id'], $data)) {
                sendResponse("User updated successfully");
            } else {
                sendError("No changes made or failed to update user", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update user", 500);
        }
    }

    public function deleteUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            sendError("User ID is required", 400);
        }

        $user = $this->userModel->findById($data['id']);
        if (!$user) {
            sendError("User not found", 404);
        }

        try {
            if ($this->userModel->deleteUserById($data['id'])) {
                sendResponse("User deleted successfully");
            } else {
                sendError("Failed to delete user", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete user", 500);
        }
    }
}
?>