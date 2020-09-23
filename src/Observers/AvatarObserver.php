<?php

namespace Saritasa\LaravelUploads\Observers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Saritasa\LaravelUploads\Services\UploadsService;

/**
 * Keep Use avatar on S3 in sync with value in DB:
 * If file was just uploaded (still resides in /tmp/ folder), then it must be moved to permanents path.
 * If user sets avatar=null, then delete original file from S3.
 */
class AvatarObserver
{
    /**
     * Helps to handle temporary uploads
     *
     * @var UploadsService
     */
    private $uploadsService;

    /**
     * Keep Use avatar on S3 in sync with value in DB
     *
     * @param UploadsService $uploadsService Helps to handle temporary uploads
     */
    public function __construct(UploadsService $uploadsService)
    {
        $this->uploadsService = $uploadsService;
    }

    /**
     * If file was just uploaded (still resides in /tmp/ folder), then it must be moved to permanents path.
     *
     * @param mixed $user User being edited
     */
    private function moveAvatarIfNeed($user): void
    {
        if (!$this->uploadsService->isTmpFile($user->avatar)) {
            return;
        }
        $permanentAvatarPath = config('media.avatars').$user->id.'.'.File::extension($user->avatar);
        if (Storage::cloud()->exists($permanentAvatarPath)) {
            Storage::cloud()->delete($permanentAvatarPath);
        }
        if (Storage::cloud()->move($user->avatar, $permanentAvatarPath)) {
            $user->avatar = $permanentAvatarPath;
            $user->save();
        }
    }

    /**
     * If user just set avatar=null, then delete original file from S3.
     *
     * @param mixed $user User being edited
     */
    private function deleteAvatarIfNeed($user): void
    {
        if (!$user->avatar && $user->isDirty('avatar')) {
            Storage::cloud()->delete($user->getOriginal('avatar'));
        }
    }

    /**
     * Handle the user "created" event.
     *
     * @param mixed $user Just created user
     */
    public function created($user): void
    {
        $this->moveAvatarIfNeed($user);
    }

    /**
     * Before existing user profile is saved in DB
     *
     * @param mixed $user User being edited
     */
    public function updating($user): void
    {
        $this->deleteAvatarIfNeed($user);
    }

    /**
     * After user profile info successfully updated in DB
     *
     * @param mixed $user User being edited
     */
    public function updated($user): void
    {
        $this->moveAvatarIfNeed($user);
    }
}
