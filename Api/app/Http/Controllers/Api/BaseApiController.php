<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Response;

class BaseApiController extends Controller
{
    public function  __construct()
    {
        
    }

    /**
     * success response method.
     * @param $result
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result)
    {
        $response = [
            'success' => true,
            'data'    => $result,
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        return response()->json($response, $code);
    }
}