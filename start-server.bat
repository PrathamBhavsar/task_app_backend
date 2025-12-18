@echo off
echo Starting Task App Backend on port 3001...
echo.
echo Server will be available at: http://localhost:3001
echo.
echo Press Ctrl+C to stop the server
echo.

php -S localhost:3001 -t public public/index.php

