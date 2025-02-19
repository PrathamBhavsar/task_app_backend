<?php

require_once 'controllers/TaskAgencyController.php';
require_once 'middleware/AuthMiddleware.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function handleTaskAgencyRoutes()
{
    authenticate();

    $taskAgencyController = new TaskAgencyController();
    $taskAgencyController->getTaskAgencies();
}
