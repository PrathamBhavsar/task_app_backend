<?php
require_once __DIR__ . '/../controllers/DesignerController.php';
require_once __DIR__ . '/../config/database.php';

function handleDesignerRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new DesignerController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createDesigner($data);
            break;

        case 'PUT':
            if (!$id)
                sendError("Designer ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateDesigner($id, $data);
            break;

        case 'DELETE':
            if (!$id)
                sendError("Designer ID required for deletion", 400);
            $controller->delete($id);
            break;

        default:
            sendError("Method not allowed", 405);
    }
}
?>