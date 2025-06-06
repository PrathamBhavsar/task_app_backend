<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/database.php';

function handleUserRoutes($method) {
    $db = (new Database())->getConnection();
    $controller = new UserController($db);

    $id = $_GET['id'] ?? null;
    $path = $_SERVER['REQUEST_URI'];

    if (strpos($path, '/api/user/register') !== false && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->register($data);
        return;
    }

    if (strpos($path, '/api/user/login') !== false && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->login($data);
        return;
    }

    switch ($method) {
    case 'GET':
        $id ? $controller->show($id) : $controller->index();
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->store($data);
        break;
    case 'PUT':
        if (!$id) sendError("User ID required for update", 400);
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->update($id, $data);
        break;
    case 'DELETE':
        if (!$id) sendError("User ID required for deletion", 400);
        $controller->delete($id);
        break;
    default:
        sendError("Method not allowed", 405);
}
}


?>
