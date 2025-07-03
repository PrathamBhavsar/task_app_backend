<?php
require_once __DIR__ . '/../controllers/MeasurementController.php';
require_once __DIR__ . '/../config/database.php';

function handleMeasurementRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new MeasurementController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            if (isset($_GET['task_id'])) {
                $controller->getAllByTaskId($_GET['task_id']);
            } elseif (isset($_GET['quote_id'])) {
                $controller->getQuoteMeasurementsByTaskId($_GET['quote_id']);
            } elseif ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createBulk($data);
            break;
        case 'PUT':
            if (!$id) sendError("Measurement ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateMeasurement($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Measurement ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
