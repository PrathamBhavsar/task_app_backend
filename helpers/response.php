<?php
function sendJson($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode(["data" => $data]);
    exit;
}

function sendError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        "error" => [
            "code" => $code,
            "message" => $message
        ]
    ]);
    exit;
}
