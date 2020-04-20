<?php

namespace Saritasa\LaravelUploads\Dto;

use Saritasa\Dto;

/**
 * The model contains data for upload file on S3.
 */
class UploadFileToS3Data extends Dto
{
    public const UPLOAD_URL = 'uploadUrl';
    public const VALID_UNTIL = 'validUntil';
    public const FILE_URL = 'fileUrl';

    /**
     * Presigned file upload url.
     *
     * @var string
     */
    public $uploadUrl;

    /**
     * Expired date for upload url.
     *
     * @var string
     */
    public $validUntil;

    /**
     * File url on S3.
     *
     * @var string
     */
    public $fileUrl;
}
