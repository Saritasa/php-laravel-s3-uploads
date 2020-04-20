<?php

namespace Saritasa\LaravelUploads\Services;

use Aws\S3\S3Client;
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
    public function getUploadFileToS3Data(string $filePath): UploadFileToS3Data
    {
        $expires = config('media.uploads.expires', '+60 minutes');

        return new UploadFileToS3Data([
            UploadFileToS3Data::UPLOAD_URL => $this->getPresignedURL($filePath, $expires),
            UploadFileToS3Data::VALID_UNTIL => Carbon::parse($expires)->format(Carbon::ISO8601),
            UploadFileToS3Data::FILE_URL => $this->s3Client->getObjectUrl($this->s3bucket, $filePath),
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
        return config('media.uploads.temp_path', 'tmp/') . $newFileName;
    }

    /**
     * Get pre-signed url for upload file.
     *
     * @param string $filePath path to upload file
     * @param string $expire expired from config for upload url
     *
     * @return string
     */
    public function getPresignedURL(string $filePath, string $expire): string
    {
        $command = $this->s3Client->getCommand('PutObject', [
            'Bucket' => $this->s3bucket,
            'Key' => $filePath,
            'ACL' => config('media.uploads.acl', 'private'),
        ]);

        return (string)$this->s3Client->createPresignedRequest($command, $expire)->getUri();
    }
}
