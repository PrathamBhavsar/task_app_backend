<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function login($data) {
        if (!isset($data['email']) || !isset($data['password'])) {
            sendError("Email and password are required", 400);
        }

        $user = $this->userModel->login($data['email'], $data['password']);

        if ($user) {
            sendJson($user, 200);
        } else {
            sendError("Invalid email or password", 401);
        }
    }

public function register($data) {
    if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
        sendError("Required fields are missing", 400);
    }

    $result = $this->userModel->register($data);

    if (is_array($result) && isset($result['error'])) {
        sendError("User already registered.", 400);
    }

    if ($result) {
        unset($result['password']); // Optional for security
        sendJson($result, 200);
    }

    sendError("Registration failed", 500);
}


    public function index() {
        $users = $this->userModel->getAll();
        sendJson($users);
    }

    public function show($id) {
        $user = $this->userModel->getById($id);
        $user ? sendJson($user) : sendError("User not found", 404);
    }

    public function store($data) {
        $success = $this->userModel->create($data);
        $success ? sendJson(["message" => "User created"]): sendError("User creation failed", 400);
    }
}
?>
