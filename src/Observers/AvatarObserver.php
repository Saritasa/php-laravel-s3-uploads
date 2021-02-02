<?php

namespace Saritasa\LaravelUploads\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * Keep Use avatar on S3 in sync with value in DB:
 * If file was just uploaded (still resides in /tmp/ folder), then it must be moved to permanents path.
 * If user sets avatar=null, then delete original file from S3.
 */
class AvatarObserver extends TmpFileObserver
{
    protected $field = 'avatar';

    /**
     * Build user avatar path. Something like 'avatars/12.jpg'
     *
     * @param Model $user Eloquent user model. Supposed, that it has fields 'id' and 'avatar'
     * @param string $fileName Current file name (s3 key)
     *
     * @return string
     */
    protected function getPermanentPath($user, string $fileName): string
    {
        return $permanentAvatarPath = config('media.avatars').$user->id.'.'.File::extension($user->avatar);
    }
}
