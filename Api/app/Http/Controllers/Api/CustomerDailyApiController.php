<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class CustomerDailyApiController extends BaseApiController
{
    public function store(Request $request)
    {
        $daily = Daily::orderBy('id', 'DESC')->limit(1)->first();
        if (empty($daily)) {
            $this->sendError('Không tìm thấy bản ghi nào!', Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'name'        => 'required|max:255'
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $cutomerById = Customer::where('id', $request['customer_id'])->first();
        if (empty($cutomerById)) {
            return $this->sendError('Customer không tồn tại !', Response::HTTP_BAD_REQUEST);
        }
        try {
            $data = [
                'daily_id'    => $daily['id'],
                'customer_id' => $request['customer_id'],
                'name'        => $request['name'],
            ];

            $customerDaily = CustomerDaily::create($data);
            if ($customerDaily) {
                return $this->sendResponse($customerDaily, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Danh sách lấy dựa theo user_key. nếu user_type = 0 => lấy toàn bộ customer_daily theo ngày.
     * Nếu user_type = 1 => lấy customer_daily của user đó theo ngày
     */
    public function getListCustomerDaily(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key'   => 'required|size:6',
            'daily_date' => 'required'
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }
        try {
            // kiểm tra user có tồn tại hay không
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            $daily = Daily::where('date', $request['daily_date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            if ($user['type'] == 0) {
                $customerDaily = CustomerDaily::where('daily_id', $daily['id'])->get();
                return $this->sendResponse($customerDaily, Response::HTTP_OK);
            }

            // lấy ra danh sách customer theo user
            $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
            if (empty($listCustomerByUser)) {
                return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
            }

            // danh sách customer_daily theo customer
            $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->get();
            return $this->sendResponse($listCustomerDaily, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }

    }


}