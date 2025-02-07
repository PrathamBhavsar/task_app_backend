<?php
header("Content-Type: application/json");
require_once 'config/auth.php'; // Calls `authenticate()` from authmiddleware.php

require_once 'routes/designer.php';
require_once 'routes/client.php';
require_once 'routes/user.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/index.php', '', $request_uri); // Remove index.php if present

switch ($request_uri) {
    case '/api/designer':
        handleDesignerRoutes($requestMethod);
        break;
    case '/api/client':
        handleClientRoutes($requestMethod);
        break;
    case '/api/user':
        handleUserRoutes($requestMethod);
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "No Route Found"]);
}
?>
