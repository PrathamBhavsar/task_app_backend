<?php
require_once __DIR__ . '/../controllers/ServiceController.php';
require_once __DIR__ . '/../config/database.php';

function handleServiceRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new ServiceController($db);

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
            $controller->createService($data);
            break;

        case 'PUT':
            if (!$id) sendError("Service ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateService($id, $data);
            break;

        case 'DELETE':
            if (!$id) sendError("Service ID required for deletion", 400);
            $controller->delete($id);
            break;

        default:
            sendError("Method not allowed", 405);
    }
}
