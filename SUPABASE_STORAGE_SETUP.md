# Supabase Storage Integration - Setup Guide

## Configuration

The Supabase storage is configured in `config/filesystems.php`. 

### Important Environment Variables

Add these to your `.env` file:

```env
# Supabase Storage Configuration
SUPABASE_ACCESS_KEY=3c16cc985beda182d5a48ae7e368d7c5
SUPABASE_SECRET_KEY=e97b462880131e0c3bdd26e525d048df07689e5808e6f082987d81bf82b993ab
SUPABASE_REGION=ap-south-1
SUPABASE_BUCKET=radiix_infiniteii
SUPABASE_ENDPOINT=https://jagmpfzdfbnafczegwvc.storage.supabase.co/storage/v1/s3
SUPABASE_URL=https://jagmpfzdfbnafczegwvc.supabase.co/storage/v1/object/public/radiix_infiniteii

# IMPORTANT: Set to true if your bucket is PUBLIC (RECOMMENDED - no API keys exposed, no expiration)
# Set to false only if bucket must be private (will use signed URLs that expire in 1 hour)
SUPABASE_PUBLIC=true
```

## How It Works

### Storage Pattern (CRITICAL)

1. **Database stores ONLY file paths** (e.g., `profile_photos/abc123.jpg`)
   - NEVER stores full URLs
   - NEVER stores signed URLs
   - Paths are stored when files are uploaded

2. **URLs are generated at runtime**
   - For **public buckets** (RECOMMENDED): Uses public URL format that never expires and doesn't expose API keys
     - Format: `https://[project-ref].supabase.co/storage/v1/object/public/[bucket]/[path]`
     - No expiration, no API keys in URL, lifetime access
   - For **private buckets**: Uses signed URLs that expire in 1 hour (exposes API keys in URL)
     - Format: `https://[project-ref].storage.supabase.co/storage/v1/s3/[bucket]/[path]?X-Amz-...`
     - Expires after 1 hour, contains API credentials in query string
   - URLs are generated fresh every time they're accessed

3. **Model Accessor**
   - `StaffUser` model has `profile_photo_url` accessor
   - Automatically generates correct URL based on bucket visibility
   - Auto-cleans paths if URLs were accidentally stored (migration helper)

### Usage in Views

Always use `$user->profile_photo_url` (not `$user->profile_photo`):

```blade
@if($user->profile_photo_url)
    <img src="{{ $user->profile_photo_url }}" alt="Profile">
@endif
```

### Usage in JSON/API Responses

The `profile_photo_url` is automatically appended to JSON responses and contains the runtime-generated URL.

The raw `profile_photo` path is hidden from JSON responses for security.

## Fixing Existing Data

If you have existing records with URLs stored in the database:

1. The model will auto-detect and clean URLs when accessed
2. URLs are automatically converted to paths and saved back to DB
3. This happens transparently on first access

## Making Your Bucket Public (Recommended)

To use public URLs (no expiration, no API keys exposed):

1. Go to your Supabase Dashboard
2. Navigate to Storage → Buckets
3. Find your `radiix_infiniteii` bucket
4. Click the three dots (⋯) → Edit
5. Toggle "Public bucket" to ON
6. Save

After making the bucket public, set `SUPABASE_PUBLIC=true` in your `.env` file.

## Testing

1. Upload a profile photo - verify only path is stored in DB
2. Check image display - verify URL is generated correctly (should be public URL format)
3. Verify URL doesn't contain API keys or expiration parameters
4. Check JSON response - verify `profile_photo_url` is present and `profile_photo` is hidden
5. Verify images load correctly and don't expire

