Write-Host "Starting Task App Backend on port 3001..." -ForegroundColor Green
Write-Host ""
Write-Host "Server will be available at: http://localhost:3001" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

php -S localhost:3001 -t public public/index.php

