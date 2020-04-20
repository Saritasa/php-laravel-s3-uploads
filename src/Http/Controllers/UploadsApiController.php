<?php

namespace Saritasa\LaravelUploads\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Saritasa\LaravelUploads\Http\Requests\GetUploadUrlRequest;
use Saritasa\LaravelUploads\Services\UploadsService;
use Dingo\Api\Http\Response;
use Exception;
use Saritasa\Transformers\BaseTransformer;

/**
 * Controller for handling uploading file.
 */
class UploadsApiController extends Controller
{
    use Helpers;
    /**
     * Uploads business-logic service.
     *
     * @var UploadsService
     */
    private $uploadsService;

    /**
     * Controller for uploading file.
     *
     * @param UploadsService $uploadsService Uploads business-logic service
     */
    public function __construct(UploadsService $uploadsService)
    {
        $this->uploadsService = $uploadsService;
    }

    /**
     * Returns signed url for uploading file to temporary folder on s3
     *
     * @param GetUploadUrlRequest $request Request for get signed url
     * @param BaseTransformer $transformer Returns data as is
     *
     * @return Response
     * @throws Exception
     */
    public function getTmpUploadUrl(GetUploadUrlRequest $request, BaseTransformer $transformer): Response
    {
        $uploadFileToS3Data = $this->uploadsService->getUploadTmpFileToS3Data($request->fileName);
        return $this->response->item($uploadFileToS3Data, $transformer);
    }
}