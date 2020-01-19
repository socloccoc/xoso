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
            $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
            if (empty($listCustomerByUser)) {
                return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
            }

            $daily = Daily::where('date', $request['date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // danh sách customer_daily theo customer
            $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->where('daily_id', $daily['id'])->pluck('id')->toArray();
            if (empty($listCustomerDaily)) {
                return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            $points = Point::where(function ($q) use ($user, $listCustomerDaily) {
                if ($user['type'] == 1) {
                    $q->whereIn('customer_daily_id', $listCustomerDaily);
                }
            })->where('type', $request['type'])->get();

            return $this->sendResponse($points, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

}