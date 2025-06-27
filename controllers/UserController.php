<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new User($db), 'user');
    }

    public function register($data)
    {
        if (empty($data['email']) || empty($data['password']) || empty($data['name']) || empty($data['address'])) {
            sendError("Required fields are missing", 400);
        }

        $result = $this->model->register($data);

        if (is_array($result) && isset($result['error'])) {
            sendError("User already registered.", 400);
        }

        if ($result) {
            unset($result['password']);
            sendJson($result, 200);
        }

        sendError("Registration failed", 500);
    }

    public function login($data)
    {
        if (!isset($data['email']) || !isset($data['password'])) {
            sendError("Email and password are required", 400);
        }

        $user = $this->model->login($data['email'], $data['password']);

        if ($user) {
            sendJson($user, 200);
        } else {
            sendError("Invalid email or password", 401);
        }
    }

    public function createUser($data)
    {
        $requiredFields = ['name', 'email', 'password'];
        parent::store($data, $requiredFields);
    }

    public function updateUser($id, $data)
    {
        $requiredFields = ['name', 'email'];
        parent::update($id, $data, $requiredFields);
    }
}
