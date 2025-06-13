<?php
require_once __DIR__ . '/../controllers/MessageController.php';
require_once __DIR__ . '/../config/database.php';

function handleMessageRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new MessageController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            if (isset($_GET['task_id'])) {
                $controller->getAllByTaskId($_GET['task_id']);
            } elseif ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createMessage($data);
            break;
        case 'PUT':
            if (!$id) sendError("Message ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateMessage($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Message ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
