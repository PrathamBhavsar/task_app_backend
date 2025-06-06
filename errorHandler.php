<?php
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    sendError("Unhandled exception occurred", 500);
});
?>
