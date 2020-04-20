<?php

namespace Saritasa\LaravelUploads\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Get upload url request. Contains file name for upload to s3.
 *
 * @property string $fileName
 */
class GetUploadUrlRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        $rules = [
            'fileName' => 'required|string',
        ];

        return $rules;
    }

    /**
     * Authorizes usage of this request.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        // If need, authorization should be defined by routes
        return true;
    }
}
