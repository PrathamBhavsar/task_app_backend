<?php
function authenticate() {
    $headers = getallheaders();

    // Check if Authorization header exists
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized: Missing token"]);
        exit;
    }

    // Extract and validate the token
    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $valid_token = "your-secure-api-token"; // Store securely in an env or config file

    if ($token !== $valid_token) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token"]);
        exit;
    }
}
?>
