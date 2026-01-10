<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StaffUser extends Model
{
    protected $fillable = [
        'username',
        'profile_photo',
        'date_of_birth',
        'phone_number',
        'remarks',
    ];

    /**
     * Hide the raw profile_photo path from JSON responses.
     * Only expose profile_photo_url (generated at runtime).
     */
    protected $hidden = ['profile_photo'];

    /**
     * Extract clean path from stored value (removes URLs if accidentally stored).
     * 
     * @return string|null
     */
    protected function getCleanProfilePhotoPath()
    {
        if (!$this->profile_photo) {
            return null;
        }
        
        $path = $this->profile_photo;
        
        // If URL was stored, extract the path
        if (str_contains($path, 'http')) {
            // Extract path from Supabase URL
            // Format: https://...supabase.co/storage/v1/object/(public|sign)/bucket/path
            if (preg_match('/\/object\/(?:public|sign)\/[^\/]+\/(.+)$/', $path, $matches)) {
                $path = $matches[1];
                // Auto-fix the database if URL was stored
                $this->updateQuietly(['profile_photo' => $path]);
            }
        }
        
        return $path;
    }

    /**
     * Get the profile photo URL.
     * Uses public Supabase URL that doesn't expire and doesn't expose API keys.
     * Format: https://[project-ref].supabase.co/storage/v1/object/public/[bucket]/[path]
     * 
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        $path = $this->getCleanProfilePhotoPath();
        
        if (!$path) {
            return null;
        }
        
        // Check if bucket is public or private
        $isPublic = config('filesystems.disks.supabase.public', true);
        
        if ($isPublic) {
            // Use public URL for public buckets (no expiration, no API keys exposed)
            // Format: https://[project-ref].supabase.co/storage/v1/object/public/[bucket]/[path]
            $baseUrl = config('filesystems.disks.supabase.url', 'https://jagmpfzdfbnafczegwvc.supabase.co/storage/v1/object/public/radiix_infiniteii');
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        } else {
            // For private buckets, generate temporary signed URL (valid for 60 minutes)
            // Note: This will expose API keys in the URL
            try {
                return Storage::disk('supabase')->temporaryUrl($path, now()->addMinutes(60));
            } catch (\Exception $e) {
                // Fallback to public URL format if temporaryUrl fails
                $baseUrl = config('filesystems.disks.supabase.url', 'https://jagmpfzdfbnafczegwvc.supabase.co/storage/v1/object/public/radiix_infiniteii');
                return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            }
        }
    }

    /**
     * Append profile_photo_url when serializing to array/JSON.
     */
    protected $appends = ['profile_photo_url'];
}
