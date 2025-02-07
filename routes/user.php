<?php

require_once 'controllers/UserController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleUserRoutes($method)
{
    authenticate(); // Check Bearer Token

    $userController = new UserController();

    switch ($method) {
        case 'GET':
            $userController->getUsers();
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data['action'])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing action"]);
                exit;
            }

            switch ($data['action']) {
                case 'create':
                    $userController->createUser();
                    break;
                case 'update':
                    $userController->updateUser();
                    break;
                case 'delete':
                    $userController->deleteUser();
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Invalid action"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    }
}
?>