<?php

namespace App\Http\Controllers\Api;

use App\Models\Daily;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class DailyApiController extends BaseApiController
{
    public function dailyLatestDate()
    {
        try {
            $daily = Daily::orderBy('id', 'DESC')->limit(1)->first();
            if (empty($daily)) {
                $this->sendError('Không tìm thấy bản ghi nào!', Response::HTTP_NOT_FOUND);
            }
            return $this->sendResponse($daily, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}