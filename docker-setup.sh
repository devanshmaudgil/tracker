#!/bin/bash

# Docker Setup Script for Radiix Infiniteii Tracker
# This script helps set up the Docker environment

set -e

echo "🚀 Setting up Docker environment for Radiix Infiniteii Tracker..."

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "⚠️  .env.example not found. Creating basic .env file..."
        cat > .env << EOF
APP_NAME="Radiix Infiniteii Tracker"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=radiix_tracker
DB_USERNAME=radiix_user
DB_PASSWORD=radiix_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

SUPABASE_ACCESS_KEY=
SUPABASE_SECRET_KEY=
SUPABASE_REGION=ap-south-1
SUPABASE_BUCKET=radiix_infiniteii
SUPABASE_URL=
SUPABASE_ENDPOINT=
SUPABASE_PUBLIC=true
EOF
    fi
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Update .env with Docker-specific settings if needed
echo "🔧 Updating .env with Docker settings..."

# Ensure database settings match docker-compose.yml
sed -i.bak 's/DB_HOST=.*/DB_HOST=mysql/' .env 2>/dev/null || true
sed -i.bak 's/DB_PORT=.*/DB_PORT=3306/' .env 2>/dev/null || true
sed -i.bak 's/DB_DATABASE=.*/DB_DATABASE=radiix_tracker/' .env 2>/dev/null || true
sed -i.bak 's/DB_USERNAME=.*/DB_USERNAME=radiix_user/' .env 2>/dev/null || true
sed -i.bak 's/DB_PASSWORD=.*/DB_PASSWORD=radiix_password/' .env 2>/dev/null || true

# Redis settings
sed -i.bak 's/REDIS_HOST=.*/REDIS_HOST=redis/' .env 2>/dev/null || true
sed -i.bak 's/REDIS_PORT=.*/REDIS_PORT=6379/' .env 2>/dev/null || true

# Clean up backup files
rm -f .env.bak

echo "✅ Environment configured"

# Build Docker images
echo "🏗️  Building Docker images..."
docker-compose build

echo "✅ Docker setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Start the containers: docker-compose up -d"
echo "   2. Run database migrations: docker-compose exec app php artisan migrate"
echo "   3. Seed the database: docker-compose exec app php artisan db:seed"
echo "   4. Access the application at: http://localhost:8000"
echo ""
echo "💡 Useful commands:"
echo "   - View logs: docker-compose logs -f"
echo "   - Stop containers: docker-compose down"
echo "   - Restart containers: docker-compose restart"
echo "   - Access app container: docker-compose exec app sh"
echo "   - Access database: docker-compose exec mysql mysql -u radiix_user -p radiix_tracker"
