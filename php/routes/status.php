<?php

require_once 'controllers/StatusController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleStatusRoutes($method)
{
    authenticate(); // Check Bearer Token

    $statusController = new StatusController();

    switch ($method) {
        case 'GET':
            $statusController->getStatuses();
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
                    $statusController->createStatus();
                    break;
                case 'update':
                    $statusController->updateStatus();
                    break;
                case 'delete':
                    $statusController->deleteStatus();
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