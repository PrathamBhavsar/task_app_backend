<?php
require_once __DIR__ . '/../controllers/DesignerController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleDesignerRoutes($method)
{
    authenticate(); // Check Bearer Token

    $designerController = new DesignerController();

    switch ($method) {
        case 'GET':
            $designerController->getDesigners();
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
                    $designerController->createDesigner();
                    break;
                case 'update':
                    $designerController->updateDesigner();
                    break;
                case 'delete':
                    $designerController->deleteDesigner();
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