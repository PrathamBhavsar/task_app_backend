<?php
require_once __DIR__ . '/../errorHandler.php';

/**
 * Authenticate API Requests
 */
function authenticate()
{
    $headers = getallheaders();

    // Check if Authorization header exists
    if (!isset($headers['Authorization'])) {
        sendError("Unauthorized: Missing token", 401);
    }

    // Extract and validate the token
    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $valid_token = "your-secure-api-token"; // Securely store in an env or config file

    if ($token !== $valid_token) {
        sendError("Unauthorized: Invalid token", 401);
    }
}
?>
