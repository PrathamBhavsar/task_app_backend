<?php
require_once __DIR__ . '/../controllers/ServiceMasterController.php';
require_once __DIR__ . '/../config/database.php';

function handleServiceMasterRoutes($method) {
    $db = (new Database())->getConnection();
    $controller = new ServiceMasterController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createServiceMaster($data);
            break;
        case 'PUT':
            if (!$id) sendError("Service Master ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateServiceMaster($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Service Master ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}


?>
