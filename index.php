<?php
header("Content-Type: application/json");

require_once 'errorHandler.php';
require_once 'helpers/response.php';
require_once 'routes/user.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($requestUri) {
    case '/api/user':
        handleUserRoutes($requestMethod);
        break;
    case '/api/user/register':
        handleUserRoutes($requestMethod);
        break;
    case '/api/user/login':
        handleUserRoutes($requestMethod);
        break;

    default:
        sendError("Route not found", 404);
}
?>
