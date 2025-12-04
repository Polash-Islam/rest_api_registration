# REST API Setup Script for Windows

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "REST API Setup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if .env exists
if (!(Test-Path ".env")) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✓ .env file created" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANT: Please update the following in .env file:" -ForegroundColor Red
    Write-Host "  - DB_PASSWORD: Your PostgreSQL password" -ForegroundColor Yellow
    Write-Host "  - MAIL_USERNAME: Your Gmail address" -ForegroundColor Yellow
    Write-Host "  - MAIL_PASSWORD: Your Gmail app password" -ForegroundColor Yellow
    Write-Host "  - MAIL_FROM_ADDRESS: Your Gmail address" -ForegroundColor Yellow
    Write-Host ""

    # Generate app key
    Write-Host "Generating application key..." -ForegroundColor Yellow
    php artisan key:generate
    Write-Host "✓ Application key generated" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host "✓ .env file already exists" -ForegroundColor Green
}

# Install composer dependencies
Write-Host "Installing Composer dependencies..." -ForegroundColor Yellow
composer install --no-interaction
Write-Host "✓ Composer dependencies installed" -ForegroundColor Green
Write-Host ""

# Check PostgreSQL connection
Write-Host "Checking database connection..." -ForegroundColor Yellow
$dbTest = php artisan migrate:status 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Database connection successful" -ForegroundColor Green
} else {
    Write-Host "✗ Database connection failed" -ForegroundColor Red
    Write-Host "  Please check your database credentials in .env" -ForegroundColor Yellow
    Write-Host "  Make sure PostgreSQL is running and database 'rest_api_db' exists" -ForegroundColor Yellow
}
Write-Host ""

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Migrations completed successfully" -ForegroundColor Green
} else {
    Write-Host "✗ Migration failed" -ForegroundColor Red
    Write-Host "  Please check the error above and fix database connection" -ForegroundColor Yellow
}
Write-Host ""

# Clear caches
Write-Host "Clearing application caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
Write-Host "✓ Caches cleared" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Update .env with your Gmail credentials" -ForegroundColor White
Write-Host "2. Start the queue worker: php artisan queue:work" -ForegroundColor White
Write-Host "3. Start the development server: php artisan serve" -ForegroundColor White
Write-Host "4. Test the API: POST http://127.0.0.1:8000/api/register" -ForegroundColor White
Write-Host ""
Write-Host "See README.md for detailed documentation" -ForegroundColor Yellow
Write-Host ""
