<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ToolStorage
{
    public const ROOT_FOLDER = 'Tool Storage';

  /** @var array<string, string> Module subfolders under Tool Storage */
    public const PATHS = [
        'users_profile_photo' => 'users/profile_photo',
        'candidates_resumes' => 'candidates/resumes',
        'candidates_documents' => 'candidates/documents',
        'tracker_reports' => 'tracker/reports',
        'tracker_attachments' => 'tracker/attachments',
        'clients_documents' => 'clients/documents',
        'imports_excel' => 'imports/excel',
    ];

    public static function modulePath(string $key): string
    {
        return self::PATHS[$key] ?? throw new \InvalidArgumentException("Unknown Tool Storage path: {$key}");
    }

    public static function rootPath(): string
    {
        return base_path(self::ROOT_FOLDER);
    }

    public static function publicUrl(string $relativePath): string
    {
        $relative = ltrim(str_replace('\\', '/', $relativePath), '/');

        return url(self::ROOT_FOLDER . '/' . $relative);
    }

    public static function absolutePath(string $relativePath): string
    {
        return self::rootPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
    }

    public static function ensureDirectory(string $relativePath): void
    {
        $directory = self::absolutePath($relativePath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    public static function deleteIfExists(?string $storedValue): void
    {
        if (! $storedValue) {
            return;
        }

        $relative = self::relativePathFromStoredValue($storedValue);
        if (! $relative) {
            return;
        }

        $absolute = self::absolutePath($relative);
        if (File::isFile($absolute)) {
            File::delete($absolute);
        }
    }

    /**
     * Store a user profile photo as user_{username}_profile_photo.{jpg|png}
     *
     * @return string Public URL saved in staff_users.profile_photo
     */
    public static function storeUserProfilePhoto(UploadedFile $file, string $username, ?string $existingStoredValue = null): string
    {
        self::deleteIfExists($existingStoredValue);

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $extension = $file->guessExtension() === 'png' ? 'png' : 'jpg';
        }
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $slug = self::slugUsername($username);
        $relativeDirectory = self::modulePath('users_profile_photo');
        $filename = "user_{$slug}_profile_photo.{$extension}";
        $relativePath = "{$relativeDirectory}/{$filename}";

        self::ensureDirectory($relativeDirectory);
        $file->move(self::absolutePath($relativeDirectory), $filename);

        return self::publicUrl($relativePath);
    }

    public static function slugUsername(string $username): string
    {
        $slug = Str::slug($username, '_');
        $slug = preg_replace('/[^a-zA-Z0-9_]/', '_', $slug ?? '') ?? 'user';
        $slug = trim($slug, '_');

        return $slug !== '' ? strtolower($slug) : 'user';
    }

    /**
     * Normalize legacy Supabase paths/URLs and local URLs to a relative Tool Storage path.
     */
    public static function relativePathFromStoredValue(string $storedValue): ?string
    {
        $value = trim($storedValue);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, 'http://') || str_contains($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH);
            if (! is_string($path) || $path === '') {
                return null;
            }

            $marker = '/' . self::ROOT_FOLDER . '/';
            $pos = stripos($path, $marker);
            if ($pos !== false) {
                return ltrim(substr($path, $pos + strlen($marker)), '/');
            }

            // Legacy Supabase object path e.g. profile_photos/xyz.jpg
            if (preg_match('/\/object\/(?:public|sign)\/[^\/]+\/(.+)$/', $path, $matches)) {
                return $matches[1];
            }

            return null;
        }

        if (str_starts_with($value, self::ROOT_FOLDER . '/')) {
            return substr($value, strlen(self::ROOT_FOLDER) + 1);
        }

        return ltrim(str_replace('\\', '/', $value), '/');
    }

    public static function urlFromStoredValue(?string $storedValue): ?string
    {
        if (! $storedValue) {
            return null;
        }

        if (str_contains($storedValue, 'http://') || str_contains($storedValue, 'https://')) {
            return $storedValue;
        }

        $relative = self::relativePathFromStoredValue($storedValue);
        if (! $relative) {
            return null;
        }

        $absolute = self::absolutePath($relative);
        if (! File::isFile($absolute)) {
            return null;
        }

        return self::publicUrl($relative);
    }
}
