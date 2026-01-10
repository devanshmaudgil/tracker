# Docker Setup Guide for Radiix Infiniteii Tracker

This guide will help you set up and run the Radiix Infiniteii Tracker application using Docker.

## Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine + Docker Compose (Linux)
- Git (optional, if cloning from repository)

## Quick Start

### 1. Setup Environment

Run the setup script to configure your environment:

```bash
# Linux/Mac
chmod +x docker-setup.sh
./docker-setup.sh

# Windows (PowerShell)
.\docker-setup.sh
```

Or manually:

```bash
# Copy environment file
cp .env.example .env

# Edit .env and update database settings to match docker-compose.yml:
# DB_HOST=mysql
# DB_DATABASE=radiix_tracker
# DB_USERNAME=radiix_user
# DB_PASSWORD=radiix_password
# REDIS_HOST=redis
```

### 2. Build and Start Containers

```bash
# Build and start all containers
docker-compose up -d

# View logs
docker-compose logs -f
```

### 3. Initialize Application

```bash
# Generate application key (if not already generated)
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed

# Create storage symlink
docker-compose exec app php artisan storage:link

# Clear and cache configuration
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### 4. Access the Application

- **Web Application**: http://localhost:8000
- **MySQL Database**: localhost:3306
  - Username: `radiix_user`
  - Password: `radiix_password`
  - Database: `radiix_tracker`
- **Redis**: localhost:6379

## Docker Services

The `docker-compose.yml` includes the following services:

1. **app** - Laravel PHP-FPM application (PHP 8.2)
2. **nginx** - Nginx web server
3. **mysql** - MySQL 8.0 database
4. **redis** - Redis cache/session storage
5. **node** - Node.js for Vite development server (development only)

## Available Commands

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f [service_name]

# Rebuild containers (after Dockerfile changes)
docker-compose build --no-cache

# Remove all containers and volumes (⚠️ removes data)
docker-compose down -v
```

### Application Commands

```bash
# Run Artisan commands
docker-compose exec app php artisan [command]

# Run Composer commands
docker-compose exec app composer [command]

# Run NPM commands
docker-compose exec app npm [command]

# Access app container shell
docker-compose exec app sh

# Run migrations
docker-compose exec app php artisan migrate

# Run seeders
docker-compose exec app php artisan db:seed

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear
```

### Database Commands

```bash
# Access MySQL shell
docker-compose exec mysql mysql -u radiix_user -p radiix_tracker

# Import database dump
docker-compose exec -T mysql mysql -u radiix_user -p radiix_tracker < dump.sql

# Export database
docker-compose exec mysql mysqldump -u radiix_user -p radiix_tracker > dump.sql
```

## Environment Variables

Key environment variables to configure in `.env`:

### Application
- `APP_ENV` - Environment (local, production)
- `APP_DEBUG` - Debug mode (true/false)
- `APP_URL` - Application URL

### Database
- `DB_CONNECTION` - Database driver (mysql)
- `DB_HOST` - Database host (mysql)
- `DB_PORT` - Database port (3306)
- `DB_DATABASE` - Database name (radiix_tracker)
- `DB_USERNAME` - Database user (radiix_user)
- `DB_PASSWORD` - Database password (radiix_password)

### Redis
- `REDIS_HOST` - Redis host (redis)
- `REDIS_PORT` - Redis port (6379)

### Supabase Storage
- `SUPABASE_ACCESS_KEY` - Supabase access key
- `SUPABASE_SECRET_KEY` - Supabase secret key
- `SUPABASE_REGION` - Supabase region
- `SUPABASE_BUCKET` - Supabase bucket name
- `SUPABASE_URL` - Supabase public URL
- `SUPABASE_ENDPOINT` - Supabase S3 endpoint

## Development vs Production

### Development Build

By default, the Docker setup uses the development target:

```bash
docker-compose up -d
```

Features:
- Hot Module Replacement (HMR) with Vite
- Debug mode enabled
- Development dependencies included
- Source maps enabled

### Production Build

To build for production:

```bash
# Update .env
APP_ENV=production
APP_DEBUG=false

# Build with production target
BUILD_TARGET=production docker-compose build

# Start containers
docker-compose up -d

# Optimize Laravel
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## Troubleshooting

### Port Already in Use

If port 8000, 3306, or 6379 is already in use, update ports in `docker-compose.yml`:

```yaml
ports:
  - "8001:80"  # Change 8000 to 8001
```

### Permission Issues

Fix storage permissions:

```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues

Ensure MySQL is healthy:

```bash
docker-compose ps mysql
docker-compose logs mysql
```

Wait for MySQL to be ready before running migrations:

```bash
# Check MySQL health
docker-compose exec mysql mysqladmin ping -h localhost -u root -p
```

### Clear All Caches

```bash
docker-compose exec app php artisan optimize:clear
```

### Rebuild Everything

```bash
# Stop and remove everything
docker-compose down -v

# Rebuild
docker-compose build --no-cache

# Start fresh
docker-compose up -d
```

## Volume Persistence

Data is persisted in Docker volumes:
- `mysql_data` - MySQL database data
- `redis_data` - Redis data

To backup volumes:

```bash
docker run --rm -v radiix_infiniteii_tracker_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql-backup.tar.gz /data
```

## File Uploads (Supabase)

The application uses Supabase for file storage (resumes, etc.). Make sure to configure Supabase credentials in `.env`:

```env
SUPABASE_ACCESS_KEY=your_access_key
SUPABASE_SECRET_KEY=your_secret_key
SUPABASE_BUCKET=radiix_infiniteii
SUPABASE_ENDPOINT=https://your-project.supabase.co/storage/v1/s3
SUPABASE_URL=https://your-project.supabase.co/storage/v1/object/public/radiix_infiniteii
```

## Support

For issues or questions:
1. Check Docker logs: `docker-compose logs`
2. Check application logs: `docker-compose exec app tail -f storage/logs/laravel.log`
3. Verify environment variables match `docker-compose.yml` settings
