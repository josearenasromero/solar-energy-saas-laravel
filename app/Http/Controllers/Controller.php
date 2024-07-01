<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected function respond($data, $statusCode = 200, $headers = [])
    {
        return response()->json($data, $statusCode, $headers);
    }

    protected function respondSuccess($data, $statusCode = 200, $headers = [])
    {
        return $this->respond([
            'success' => true,
            'data' => $data,
            'meta' => null,
            'error' => null,
        ], $statusCode, $headers);
    }

    protected function respondSuccessWithPagination($data, $total, $statusCode = 200, $headers = [])
    {
        return $this->respond([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $total,
            ],
            'error' => null,
        ], $statusCode, $headers);
    }

    protected function respondSuccessWithPaginationNested($data, $total, $totalParent, $statusCode = 200, $headers = [])
    {
        return $this->respond([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $total,
                'total_parent' => $totalParent,
            ],
            'error' => null,
        ], $statusCode, $headers);
    }

    protected function respondError($message, $status, $code = '')
    {
        return $this->respond([
            'success' => false,
            'data' => null,
            'meta' => null,
            'error' => [
                'message' => trans($message),
                'status' => $status,
                'code' => $code,
            ],
        ], $status);
    }

    protected function respondUnauthorized($message = 'Unauthorized', $code = '')
    {
        return $this->respondError($message, 401, $code);
    }

    protected function respondForbidden($message = 'Forbidden', $code = '')
    {
        return $this->respondError($message, 403, $code);
    }

    protected function respondNotFound($message = 'Not Found', $code = '')
    {
        return $this->respondError($message, 404, $code);
    }

    protected function respondUnprocessableEntity($message = 'Unprocessable Entity', $code = '')
    {
        return $this->respondError($message, 422, $code);
    }

    protected function respondInternalError($message = 'Internal Error', $code = '')
    {
        return $this->respondError($message, 500, $code);
    }
}
