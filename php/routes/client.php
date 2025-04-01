<?php

require_once 'controllers/ClientController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleClientRoutes($method)
{
    authenticate(); // Check Bearer Token

    $clientController = new ClientController();

    switch ($method) {
        case 'GET':
            $clientController->getClients();
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
                    $clientController->createClient();
                    break;
                case 'update':
                    $clientController->updateClient();
                    break;
                case 'delete':
                    $clientController->deleteClient();
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