<?php

namespace Saritasa\LaravelUploads\Traits;

use Illuminate\Support\Facades\Storage;

trait HasUploadedFile
{
    /**
     * Convert image path on S3 bucket to pre-signed read URL
     *
     * @param string|null $imagePath Path inside S3 bucket
     *
     * @return string|null
     */
    public function fileUrl(?string $imagePath): ?string
    {
        if (!$imagePath || strpos($imagePath, '://') !== false) {
            return $imagePath;
        }
        return Storage::cloud()->temporaryUrl(trim($imagePath, '/'), config('media.expires'));
    }
}
