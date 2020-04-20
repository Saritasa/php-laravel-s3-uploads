<?php

namespace Saritasa\LaravelUploads\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Saritasa\Dto;

class UploadTransformer extends TransformerAbstract
{
    public function transform(Dto $data)
    {
        return $data->toArray();
    }
}
