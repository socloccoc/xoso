<?php

namespace App\Http\Controllers\Api;

use App\Models\Daily;
use App\Models\Result;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class ResultApiController extends BaseApiController
{
    public function result($date)
    {
        try {
            $result = Result::where('date', $date)->first();
            $result = json_decode($result['result'], true);
            if (empty($result)) {
                $this->sendError('Không tìm thấy bản ghi nào!', Response::HTTP_NOT_FOUND);
            }
            return $this->sendResponse($result, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}
