<?php

// Simple error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}");
    
    if (error_reporting() & $errno) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    return false;
});

set_exception_handler(function ($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    
    http_response_code(500);
    header('Content-Type: application/json');
    
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $_ENV['APP_DEBUG'] ?? false ? $exception->getMessage() : 'An error occurred'
    ]);
    
    exit;
});
