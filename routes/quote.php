<?php
require_once __DIR__ . '/../controllers/QuoteController.php';
require_once __DIR__ . '/../config/database.php';

function handleQuoteRoutes($method)
{
    $db = (new Database())->getConnection();
    $controller = new QuoteController($db);

    $id = $_GET['id'] ?? null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->createQuote($data);
            break;
        case 'PUT':
            if (!$id) sendError("Quote ID required for update", 400);
            $data = json_decode(file_get_contents("php://input"), true);
            $controller->updateQuote($id, $data);
            break;
        case 'DELETE':
            if (!$id) sendError("Quote ID required for deletion", 400);
            $controller->delete($id);
            break;
        default:
            sendError("Method not allowed", 405);
    }
}
