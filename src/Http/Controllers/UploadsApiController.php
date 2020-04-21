<?php

namespace Saritasa\LaravelUploads\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Saritasa\LaravelUploads\Http\Requests\GetUploadUrlRequest;
use Saritasa\LaravelUploads\Http\Transformers\UploadTransformer;
use Saritasa\LaravelUploads\Services\UploadsService;
use Dingo\Api\Http\Response;
use Exception;

/**
 * Controller for temporary file uploads.
 */
class UploadsApiController extends Controller
{
    use Helpers;

    /**
     * Returns signed url for uploading file to temporary folder on s3
     *
     * @param GetUploadUrlRequest $request Request for get signed url
     * @param UploadsService $uploadsService Implements generating of singed URLs
     * @param UploadTransformer $transformer Returns data as is
     *
     * @return Response
     * @throws Exception
     */
    public function getTmpUploadUrl(
        GetUploadUrlRequest $request,
        UploadsService $uploadsService,
        UploadTransformer $transformer
    ): Response {
        $uploadFileToS3Data = $uploadsService->getUploadTmpFileToS3Data($request->fileName);
        return $this->response->item($uploadFileToS3Data, $transformer);
    }
}
