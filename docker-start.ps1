# Docker Start Script for Windows PowerShell
# This script helps start the Docker environment

Write-Host "🚀 Starting Radiix Infiniteii Tracker Docker Environment..." -ForegroundColor Cyan

# Check if Docker is running
$dockerRunning = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Docker is not running. Please start Docker Desktop." -ForegroundColor Red
    exit 1
}

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "📝 Creating .env file..." -ForegroundColor Yellow
    if (Test-Path .env.example) {
        Copy-Item .env.example .env
    } else {
        Write-Host "⚠️  .env.example not found. Please create .env manually." -ForegroundColor Yellow
    }
}

# Update .env with Docker settings
Write-Host "🔧 Updating .env with Docker settings..." -ForegroundColor Yellow
if (Test-Path .env) {
    $envContent = Get-Content .env -Raw
    
    # Update database settings
    $envContent = $envContent -replace 'DB_HOST=.*', 'DB_HOST=mysql'
    $envContent = $envContent -replace 'DB_PORT=.*', 'DB_PORT=3306'
    $envContent = $envContent -replace 'DB_DATABASE=.*', 'DB_DATABASE=radiix_tracker'
    $envContent = $envContent -replace 'DB_USERNAME=.*', 'DB_USERNAME=radiix_user'
    $envContent = $envContent -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=radiix_password'
    
    # Update Redis settings
    $envContent = $envContent -replace 'REDIS_HOST=.*', 'REDIS_HOST=redis'
    $envContent = $envContent -replace 'REDIS_PORT=.*', 'REDIS_PORT=6379'
    
    Set-Content .env -Value $envContent -NoNewline
    Write-Host "✅ Environment configured" -ForegroundColor Green
}

# Build and start containers
Write-Host "🏗️  Building Docker images..." -ForegroundColor Cyan
docker-compose build

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Build successful!" -ForegroundColor Green
} else {
    Write-Host "❌ Build failed!" -ForegroundColor Red
    exit 1
}

Write-Host "🚀 Starting containers..." -ForegroundColor Cyan
docker-compose up -d

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Containers started!" -ForegroundColor Green
    
    # Wait a bit for services to be ready
    Write-Host "⏳ Waiting for services to be ready..." -ForegroundColor Yellow
    Start-Sleep -Seconds 5
    
    # Run initial setup
    Write-Host "📦 Running initial setup..." -ForegroundColor Cyan
    docker-compose exec -T app php artisan key:generate --force 2>$null
    docker-compose exec -T app php artisan migrate --force 2>$null
    docker-compose exec -T app php artisan storage:link 2>$null
    
    Write-Host ""
    Write-Host "✅ Setup complete!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Application Information:" -ForegroundColor Cyan
    Write-Host "   🌐 Web Application: http://localhost:8000" -ForegroundColor White
    Write-Host "   🗄️  MySQL: localhost:3306" -ForegroundColor White
    Write-Host "   📊 Database: radiix_tracker" -ForegroundColor White
    Write-Host "   👤 Username: radiix_user" -ForegroundColor White
    Write-Host ""
    Write-Host "💡 Useful commands:" -ForegroundColor Cyan
    Write-Host "   View logs: docker-compose logs -f" -ForegroundColor White
    Write-Host "   Stop: docker-compose down" -ForegroundColor White
    Write-Host "   Shell access: docker-compose exec app sh" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "❌ Failed to start containers!" -ForegroundColor Red
    exit 1
}
