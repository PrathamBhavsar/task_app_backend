<?php
require_once __DIR__ . '/../controllers/TimelineController.php';
require_once __DIR__ . '/../config/database.php';

function handleTimelineRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new TimelineController($db);

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
            $controller->createTimeline($data);
            break;
        case 'PUT':
            if (!$id) sendError("Timeline ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateTimeline($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Timeline ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
