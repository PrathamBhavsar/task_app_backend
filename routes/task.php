<?php
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../config/database.php';

function handleTaskRoutes($method) {
    $db = (new Database())->getConnection();
    $controller = new TaskController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            if (isset($_GET['detailed'])) {
                $controller->detailedTask($_GET['id']);
            } else if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createTask($data);
            break;
       case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$id) sendError("Task ID required for update", 400);

            if (isset($_GET['status'])) {
                if (!isset($data['status_id']) || !isset($data['user_id'])) {
                    sendError("status_id and user_id are required", 400);
                }
                $controller->updateStatus($id, $data['status_id'], $data['user_id']);
            } else {
                $controller->updateTask($id, $data);
            }
            break;
        case 'DELETE':
            if (!$id) sendError("Task ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
?>
