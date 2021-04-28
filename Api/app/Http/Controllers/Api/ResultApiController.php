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
            $data = [];
            foreach ($result as $index => $item){
                if($index == "lo"){
                    foreach ($item as $i){
                        $subData = $i;
                        $subData['type'] = 0;
                        $data[] = $subData;
                    }
                }
                if($index == "de"){
                    $subData = $item;
                    $subData['type'] = 1;
                    $data[] = $subData;
                }
                if($index == "bacang"){
                    $subData = $item;
                    $subData['type'] = 4;
                    $data[] = $subData;
                }
            }
            return $this->sendResponse($data, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}
