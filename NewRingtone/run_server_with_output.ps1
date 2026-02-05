# Run server and show all output
Write-Host "Starting server with full output..." -ForegroundColor Yellow
Write-Host "This will show all PHP errors and warnings" -ForegroundColor Gray
Write-Host "Press Ctrl+C to stop" -ForegroundColor Gray
Write-Host ""

# Start server and capture output
php -S localhost:3000 -t public 2>&1 | ForEach-Object {
    Write-Host $_ -ForegroundColor Cyan
}

