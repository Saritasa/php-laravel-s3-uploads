<?php

namespace Saritasa\LaravelUploads\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

trait HasUploadedFile
{
    /**
     * Convert file path on S3 bucket to pre-signed read URL
     *
     * @param string|null $filePath Path inside S3 bucket
     *
     * @return string|null
     */
    public function fileUrl(?string $filePath): ?string
    {
        if (!$filePath || strpos($filePath, '://') !== false) {
            return $filePath;
        }
        $cloud = Storage::cloud();

        $adapter = $cloud->getAdapter();
        if ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsTemporaryUrl($adapter, trim($filePath, '/'), config('media.expires'), [
                'start_time' => Carbon::now()->setMinute(0)->setSeconds(0)->setMilliseconds(0),
            ]);
        } elseif (method_exists($cloud->getAdapter(), 'getTemporaryUrl')) {
            return $cloud->temporaryUrl(trim($filePath, '/'), config('media.expires'));
        } else {
            return $cloud->url($filePath);
        }
    }

    /**
     * Fixed version of FilesystemAdapter->getAwsTemporaryUrl() method:
     * original method doesn't pass [options] argument to $client->createPresignedRequest() method,
     * thus, it is impossible to create cacheable presigned URLs
     *
     * @param AwsS3Adapter $adapter S3 Adapter
     * @param string $path File path inside S3 bucket
     * @param string|DateTimeInterface $expiration When link should become invalid
     * @param array $options Other options (ex. when is "start_time" of expiration base)
     *
     * @return string
     */
    private function getAwsTemporaryUrl(AwsS3Adapter $adapter, $path, $expiration, array $options)
    {
        $client = $adapter->getClient();

        $command = $client->getCommand('GetObject', array_merge([
            'Bucket' => $adapter->getBucket(),
            'Key' => $adapter->getPathPrefix().$path,
        ], $options));

        return (string) $client->createPresignedRequest($command, $expiration, $options)->getUri();
    }
}
