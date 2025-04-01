<?php
/**
 * Send a formatted JSON success response
 */
function sendResponse($message, $data = [], $code = 200)
{
    http_response_code($code);
    echo json_encode([
        "status" => "success",
        "code" => $code,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}
?>
