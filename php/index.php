<?php
header("Content-Type: application/json");
require_once 'errorHandler.php';
require_once 'config/auth.php';
require_once 'middleware/AuthMiddleware.php';

require_once 'routes/designer.php';
require_once 'routes/client.php';
require_once 'routes/user.php';
require_once 'routes/status.php';
require_once 'routes/priority.php';
require_once 'routes/task.php';
require_once 'routes/taskSalesperson.php';
require_once 'routes/taskAgency.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/index.php', '', $requestUri);

try {
    switch ($requestUri) {
        case '/api/designer':
            handleDesignerRoutes($requestMethod);
            break;
        case '/api/client':
            handleClientRoutes($requestMethod);
            break;
        case '/api/user':
            handleUserRoutes($requestMethod);
            break;
        case '/api/status':
            handleStatusRoutes($requestMethod);
            break;
        case '/api/priority':
            handlePriorityRoutes($requestMethod);
            break;
        case '/api/task':
            handleTaskRoutes($requestMethod);
            break;
        case '/api/taskSalesperson':
            handleTaskSalespersonRoutes();
            break;
        case '/api/taskAgency':
            handleTaskAgencyRoutes();
            break;
        default:
            sendError("No Route Found", 404);
    }
} catch (Exception $e) {
    sendError("Internal Server Error", 500);
}
?>
