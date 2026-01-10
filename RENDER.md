# Deploying to Render

This guide will help you deploy the Radiix Infiniteii Tracker application to Render.

## Prerequisites

1. A Render account (sign up at [render.com](https://render.com))
2. Your GitHub/GitLab repository connected to Render
3. Supabase credentials (for file storage)

## Quick Setup

### Option 1: Using Render Dashboard (Recommended)

1. **Create a New Web Service**
   - Go to your Render dashboard
   - Click "New +" → "Web Service"
   - Connect your repository
   - Select your branch

2. **Configure Build Settings**
   - **Environment**: Select "Docker"
   - **Dockerfile Path**: Use `Dockerfile.render` (or `Dockerfile` if preferred)
   - **Build Command**: Leave empty (handled by Dockerfile)
   - **Start Command**: `php-fpm` (Render will handle this, but specify for clarity)

3. **Create Database**
   - Go to "New +" → "PostgreSQL" (or MySQL if preferred)
   - Note the connection details (you'll need them for environment variables)

4. **Configure Environment Variables**
   Add these in the Render dashboard under "Environment":
   
   ```
   APP_NAME="Radiix Infiniteii Tracker"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=              # Generate with: php artisan key:generate
   APP_URL=https://your-app-name.onrender.com
   
   DB_CONNECTION=mysql
   DB_HOST=              # From your database service
   DB_PORT=3306
   DB_DATABASE=          # From your database service
   DB_USERNAME=          # From your database service
   DB_PASSWORD=          # From your database service
   
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   
   SUPABASE_ACCESS_KEY=your_access_key
   SUPABASE_SECRET_KEY=your_secret_key
   SUPABASE_REGION=ap-south-1
   SUPABASE_BUCKET=radiix_infiniteii
   SUPABASE_URL=https://your-project.supabase.co/storage/v1/object/public/radiix_infiniteii
   SUPABASE_ENDPOINT=https://your-project.supabase.co/storage/v1/s3
   SUPABASE_PUBLIC=true
   ```

5. **Deploy**
   - Click "Create Web Service"
   - Wait for the build to complete
   - Check logs if there are any issues

### Option 2: Using render.yaml (Infrastructure as Code)

1. **Update render.yaml** with your specific settings:
   - Update database configuration
   - Adjust service name and region as needed

2. **Create Blueprint**
   - In Render dashboard, go to "Blueprints"
   - Click "New Blueprint"
   - Connect your repository
   - Render will automatically detect and use `render.yaml`

## Important Notes

### PHP Version
The application requires **PHP 8.4** due to dependency requirements. Make sure your Dockerfile uses:
```dockerfile
FROM php:8.4-fpm-alpine
```

### Database Migrations
After deployment, you'll need to run migrations:

```bash
# Via Render Shell
render ssh <service-name>
php artisan migrate --force

# Or via Render Dashboard
# Add a Deploy Script:
php artisan migrate --force
```

### Application Key
If `APP_KEY` is not set, generate it:
```bash
php artisan key:generate --force
```

### Storage Link
Create the storage symlink:
```bash
php artisan storage:link
```

## Render-Specific Configuration

### Build Settings
- **Dockerfile**: Use `Dockerfile.render` for optimized builds
- **Build Command**: Empty (handled by Dockerfile)
- **Start Command**: `php-fpm` (though Render will handle this)

### Health Checks
The application includes a health check endpoint at `/up`. Configure this in Render:
- **Health Check Path**: `/up`

### Environment Variables
Render provides some environment variables automatically:
- `DATABASE_URL` - If using Render's managed database
- `RENDER` - Always set to `true` on Render

### Persistent Storage
Render doesn't support persistent volumes for web services. Use Supabase or another cloud storage for file uploads (already configured).

## Troubleshooting

### Build Fails with PHP Version Error
- Ensure `Dockerfile.render` uses `php:8.4-fpm-alpine`
- Check `composer.json` has `"php": "^8.4"`

### Composer Install Fails
- Check logs for specific dependency issues
- Try updating `composer.lock` locally first:
  ```bash
  composer update --no-dev --prefer-dist
  git add composer.lock
  git commit -m "Update composer.lock for PHP 8.4"
  ```

### Database Connection Issues
- Verify database environment variables match your Render database
- Check database service is running and accessible
- Test connection from Render Shell:
  ```bash
  php artisan tinker
  DB::connection()->getPdo();
  ```

### Application Not Loading
- Check `APP_URL` matches your Render service URL
- Verify `APP_KEY` is set
- Check logs: `render logs <service-name>`
- Run health check: `curl https://your-app.onrender.com/up`

### 502 Bad Gateway
- Verify PHP-FPM is running
- Check application logs in Render dashboard
- Ensure environment variables are correctly set

## Post-Deployment Steps

1. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

2. **Seed Database** (optional)
   ```bash
   php artisan db:seed --force
   ```

3. **Cache Configuration** (for performance)
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

## Performance Optimization

For production, enable these optimizations:

1. **Enable OPcache** (should be enabled by default in PHP 8.4)
2. **Cache Configuration**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Queue Workers** (if using queues):
   Create a separate Background Worker service:
   ```yaml
   - type: worker
     name: radiix-tracker-worker
     env: docker
     dockerfilePath: ./Dockerfile.render
     startCommand: php artisan queue:work
   ```

## Monitoring

- **Logs**: View in Render Dashboard → Your Service → Logs
- **Metrics**: Available in Render Dashboard → Metrics tab
- **Health Checks**: Configured at `/up` endpoint

## Support

For Render-specific issues:
- [Render Documentation](https://render.com/docs)
- [Render Community](https://community.render.com)

For application issues, check:
- Application logs in Render dashboard
- `storage/logs/laravel.log` (if accessible)
