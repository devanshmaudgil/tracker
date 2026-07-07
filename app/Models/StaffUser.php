<?php

namespace App\Models;

use App\Services\Storage\ToolStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StaffUser extends Model
{
    protected $fillable = [
        'username',
        'email',
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

    protected function getCleanProfilePhotoPath()
    {
        if (! $this->profile_photo) {
            return null;
        }

        $path = $this->profile_photo;

        if (str_contains($path, 'http')) {
            if (preg_match('/\/object\/(?:public|sign)\/[^\/]+\/(.+)$/', $path, $matches)) {
                $path = $matches[1];
                $this->updateQuietly(['profile_photo' => $path]);
            } elseif ($toolRelative = ToolStorage::relativePathFromStoredValue($path)) {
                $path = $toolRelative;
            }
        }

        return $path;
    }

    public function getProfilePhotoUrlAttribute()
    {
        if (! $this->profile_photo) {
            return null;
        }

        // Local Tool Storage URL/path
        $localUrl = ToolStorage::urlFromStoredValue($this->profile_photo);
        if ($localUrl) {
            return $localUrl;
        }

        // Legacy Supabase fallback for existing records not yet re-uploaded
        $path = $this->getCleanProfilePhotoPath();
        if (! $path || str_contains((string) $this->profile_photo, ToolStorage::ROOT_FOLDER)) {
            return null;
        }

        $isPublic = config('filesystems.disks.supabase.public', true);

        if ($isPublic) {
            $baseUrl = config('filesystems.disks.supabase.url', 'https://jagmpfzdfbnafczegwvc.supabase.co/storage/v1/object/public/radiix_infiniteii');

            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }

        try {
            return Storage::disk('supabase')->temporaryUrl($path, now()->addMinutes(60));
        } catch (\Exception $e) {
            $baseUrl = config('filesystems.disks.supabase.url', 'https://jagmpfzdfbnafczegwvc.supabase.co/storage/v1/object/public/radiix_infiniteii');

            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
    }

    protected $appends = ['profile_photo_url'];

    public function loginAccount()
    {
        return $this->hasOne(UserLogin::class, 'staff_user_id');
    }
}
