<?php

namespace Saritasa\LaravelUploads\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Saritasa\LaravelUploads\Services\UploadsService;

abstract class TmpFileObserver
{
    /**
     * Eloquent model field to monitor as S3 file reference
     *
     * @var string
     */
    protected $field;

    /**
     * When file with new name is being saved, delete or not previous file
     *
     * @var bool
     */
    protected $deleteOld = true;

    /**
     * When move file error happens, throw exception or ignore it silently
     *
     * @var bool
     */
    protected $throwMoveExceptions = true;

    /**
     * When delete file error happens, throw exception or ignore it silently
     *
     * @var bool
     */
    protected $throwDeleteExceptions = false;

    /**
     * Helps to handle temporary uploads.
     *
     * @var UploadsService
     */
    private $uploadsService;

    /**
     * Keep use file on S3 in sync with value in DB.
     *
     * @param UploadsService $uploadsService Helps to handle temporary uploads
     */
    public function __construct(UploadsService $uploadsService)
    {
        $this->uploadsService = $uploadsService;
    }

    /**
     * Get permanent file storage path - depends on tracked entity
     *
     * @param Model $model Eloquent model
     * @param string $fileName Current file path
     *
     * @return string
     */
    abstract protected function getPermanentPath(Model $model, string $fileName): string;

    /**
     * If field set as null, then delete original file from S3.
     *
     * @param Model $model Tracked model
     *
     * @throws Exception
     */
    private function deleteIfNeed(Model $model): void
    {
        if (!$model->{$this->field} && $model->isDirty($this->field)) {
            $path = $model->getOriginal($this->field);
            try {
                Storage::cloud()->delete($path);
                Log::info("File deleted: $path.");
            } catch (Exception $exception) {
                Log::error("Could not delete file $path", compact('exception'));
                if ($this->throwDeleteExceptions) {
                    throw $exception;
                }
            }
        }
    }


    /**
     * If file is in temporary storage (ex. has /tmp/ prefix in S3 bucket),
     * then it should be moved to permanent storage path and model should be updated
     *
     * @param Model $model Tracked Eloquent model
     *
     * @return boolean
     *
     * @throws Exception
     */
    private function moveIfNeed(Model $model): bool
    {
        $currentPath = $model->{$this->field};
        if (!$this->uploadsService->isTmpFile($currentPath)) {
            return false;
        }
        $permanentPath = $this->getPermanentPath($model, $currentPath);
        $s3 = Storage::cloud();
        if ($s3->exists($permanentPath)) {
            try {
                $s3->delete($permanentPath);
                Log::info("Older file was deleted $permanentPath");
            } catch (Exception $exception) {
                Log::error("Error deleting file $permanentPath", compact('exception'));
            }
        }
        try {
            if ($s3->move($currentPath, $permanentPath)) {
                Log::info("Judge certificate image $currentPath moved to $permanentPath.");
                $model->{$this->field} = $permanentPath;
                return true;
            }
        } catch (Exception $exception) {
            Log::error("Could not move file $currentPath to $permanentPath.");
            if ($this->throwMoveExceptions) {
                throw $exception;
            }
        }
        return false;
    }

    /**
     * After entity was successfully created, we can get its ID to create permanent path and move file.
     *
     * @param Model $model Just created entity
     */
    public function created(Model $model): void
    {
        if ($this->moveIfNeed($model)) {
            $model->save();
        }
    }

    /**
     * When we are updating entity, if file is in TMP storage, move it to avoid saving entity twice
     *
     * @param Model $model Model being edited
     */
    public function updating(Model $model): void
    {
        $this->moveIfNeed($model);
    }

    /**
     * After model was successfully updated in DB - if related file was de-assigned, delete it from S3
     *
     * @param Model $model Just updated model
     */
    public function updated(Model $model): void
    {
        if (!$this->deleteOld) {
            return;
        }
        $this->deleteIfNeed($model);
    }
}
