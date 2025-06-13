<?php
require_once __DIR__ . '/../controllers/TaskStatusController.php';
require_once __DIR__ . '/../config/database.php';

function handleTaskStatusRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new TaskStatusController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createTaskStatus($data);
            break;
        case 'PUT':
            if (!$id) sendError("Status ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateTaskStatus($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Status ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
