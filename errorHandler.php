<?php
/**
 * Send a formatted JSON error response
 */
function sendError($message, $code = 400, $errors = [])
{
    http_response_code($code);
    echo json_encode([
        "status" => "error",
        "code" => $code,
        "message" => $message,
        "errors" => $errors
    ]);
    exit;
}

/**
 * Global Exception Handler (for unexpected errors)
 */
set_exception_handler(function ($exception) {
    error_log($exception->getMessage()); // Log error to file (for debugging)
    sendError("An unexpected error occurred. Please try again later.", 500);
});

/**
 * Global Error Handler (for fatal errors)
 */
set_error_handler(function ($severity, $message, $file, $line) {
    error_log("Error: [$severity] $message in $file on line $line");
    sendError("A system error occurred. Please contact support.", 500);
});

/**
 * Enable error reporting only in development
 */
// if ($_ENV['APP_ENV'] === 'development') {
//     ini_set('display_errors', 1);
//     error_reporting(E_ALL);
// } else {
    ini_set('display_errors', 0);
    error_reporting(0);
// }
?>
