<?php

require_once 'controllers/PriorityController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handlePriorityRoutes($method)
{
    authenticate(); // Check Bearer Token

    $userController = new PriorityController();

    switch ($method) {
        case 'GET':
            $userController->getPriorities();
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
                    $userController->createPriority();
                    break;
                case 'update':
                    $userController->updatePriority();
                    break;
                case 'delete':
                    $userController->deletePriority();
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