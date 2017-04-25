<?php
namespace Hug\Group\Http\Responses;

use Exception;
use Hug\Group\Support\Traits\Macroable;
use Hug\Group\Http\Responses\ApiResponse;

class ResponseFactory
{
    use Macroable;

    public function detailApi($mContent = [])
    {
        return ApiResponse::detailApi($mContent);
    }

    public function listApi($mContent = [])
    {
        return ApiResponse::listApi($mContent);
    }

    public function exceptionApi(Exception $mContent)
    {
        return ApiResponse::exceptionApi($mContent);
    }
}
