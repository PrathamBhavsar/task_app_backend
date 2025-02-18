<?php
require_once 'controllers/TaskController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleTaskRoutes($method)
{
    authenticate(); // Check Bearer Token

    $taskController = new TaskController();
    $data = json_decode(file_get_contents("php://input"), true);

    // Handle GET requests
    if ($method === 'GET') {
        $taskController->getTasks();
        exit;
    }

    // Validate JSON input
    if ($method === 'POST') {
        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid or missing JSON body"]);
            exit;
        }

        if (!isset($data['action'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing action"]);
            exit;
        }

        switch ($data['action']) {
            case 'specific':
                if (!isset($data['id'])) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "User ID is required"]);
                    break;
                }
                $taskController->specific();
                break;
            
            case 'create':
                if (!validateTaskData($data)) break;
                $taskController->createTask($data);
                break;

            case 'update':
                if (!isset($data['id']) || !validateTaskData($data)) break;
                $taskController->updateTask($data);
                break;

            case 'delete':
                if (!isset($data['id'])) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Missing task ID"]);
                    break;
                }
                $taskController->deleteTask($data['id']);
                break;

            default:
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid action"]);
        }
        exit;
    }

    // Handle unsupported methods
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
}

/**
 * Validate required task data fields
 */
function validateTaskData($data)
{
    $requiredFields = ['deal_no', 'name', 'start_date', 'due_date', 'priority', 'created_by', 'status'];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing or empty field: $field"]);
            return false;
        }
    }
    return true;
}
?>
