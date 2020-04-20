<?php

namespace Saritasa\LaravelUploads\Services;

use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Saritasa\LaravelUploads\Dto\UploadFileToS3Data;
use Carbon\Carbon;
use Exception;
use File;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Ramsey\Uuid\Uuid;

/**
 * Uploads service. Allows to perform read, store, delete files and retrieve url to file.
 */
class UploadsService
{
    /**
     * Bucket
     *
     * @var string
     */
    private $s3bucket;

    /**
     * Temporary uploads path
     *
     * @var string
     */
    private $tmpPath;

    /**
     * S3 Client
     *
     * @var S3Client
     */
    private $s3Client;

    /**
     * Uploads service. Allows to perform retrieve url to file.
     *
     * @param AwsS3Adapter $adapter Pre-configured AWS S3 adapter
     */
    public function __construct(AwsS3Adapter $adapter)
    {
        $this->tmpPath = trim(config('media.uploads.temp_path', 'tmp'), '/').'/';
        $this->s3bucket = $adapter->getBucket();
        $this->s3Client = $adapter->getClient();
    }

    /**
     * Return data for upload file to s3 temporary directory.
     *
     * @param string $name file name
     *
     * @return UploadFileToS3Data
     */
    public function getUploadTmpFileToS3Data(string $name): UploadFileToS3Data
    {
        $file = $this->generateTmpFilePath($name);
        return $this->getUploadFileToS3Data($file);
    }

    /**
     * Return data for upload file to s3.
     *
     * @param string $filePath Name and path of uploaded file
     *
     * @return UploadFileToS3Data
     */
    protected function getUploadFileToS3Data(string $filePath): UploadFileToS3Data
    {
        $uploadExpres = config('media.uploads.expires', '+60 minutes');
        $readExpires = config('media.expires', '+24 hours');

        return new UploadFileToS3Data([
            UploadFileToS3Data::UPLOAD_URL => $this->signedUrl('Put', $filePath, $uploadExpres),
            UploadFileToS3Data::VALID_UNTIL => Carbon::parse($uploadExpres)->format(Carbon::ISO8601),
            UploadFileToS3Data::FILE_URL => $this->signedUrl('Put', $filePath, $readExpires),
        ]);
    }

    /**
     * Generate file path for upload file to temporary directory.
     *
     * @param string $name file name
     *
     * @return string
     */
    private function generateTmpFilePath(string $name): string
    {
        try {
            $newFileName = Uuid::uuid4()->toString() .'.'. File::extension($name);
        } catch (Exception $e) {
            $newFileName = File::basename($name);
        }
        return $this->tmpPath . $newFileName;
    }

    /**
     * Get pre-signed url for upload file.
     *
     * @param string $method Get, Put or Post
     * @param string $filePath path to upload file
     * @param string $expire expired from config for upload url
     *
     * @return string
     */
    protected function signedUrl(string $method, string $filePath, string $expire): string
    {
        $command = $this->s3Client->getCommand(Str::studly($method).'Object', [
            'Bucket' => $this->s3bucket,
            'Key' => $filePath,
        ]);

        return (string)$this->s3Client->createPresignedRequest($command, $expire)->getUri();
    }

    /**
     * Remove storage prefix from given path.
     *
     * @param string|null $fileUrl Url to remove prefix from
     *
     * @return string
     */
    public function getPathFromUrl(?string $fileUrl): string
    {
        if (!$fileUrl) {
            return null;
        }
        $pathPrefix = trim($this->s3Client->getObjectUrl($this->s3bucket, '/'), '/').'/';
        $path = Str::replaceFirst($pathPrefix, '', $fileUrl);
        return (new Uri($path))->getPath();
    }

    /**
     * Determine, if file is in temporary files folder
     *
     * @param string|null $filePath File path within S3 bucket
     *
     * @return boolean
     */
    public function isTmpFile(?string $filePath): bool
    {
        if (!$filePath) {
            return false;
        }
        return Str::is($this->tmpPath."*", $filePath);
    }
}
