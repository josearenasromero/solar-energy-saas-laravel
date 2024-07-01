<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class NotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json(
            [
                'status' => false,
                'data' => null,
                'meta' => null,
                'error' => [
                    'message' => trans($this->getMessage()),
                    'code' => $this->getCode(),
                    'status' => Response::HTTP_NOT_FOUND 
                ]
            ], 
            Response::HTTP_NOT_FOUND);
    }
}