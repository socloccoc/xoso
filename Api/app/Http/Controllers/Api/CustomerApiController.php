<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class CustomerApiController extends BaseApiController
{
    /**
     * Store
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key'    => 'required|size:6',
            'name'        => 'required|max:255',
            'lo_rate'     => 'required|numeric',
            'de_rate'     => 'required|numeric',
            'de_percent'  => 'required|numeric',
            'xien_rate'   => 'required|numeric',
            'bacang_rate' => 'required|numeric'
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại!', Response::HTTP_NOT_FOUND);
            }

            if($this->checkCustomerExist($user['id'], $request['name'])){
                return $this->sendError('Customer đã tồn tại!', Response::HTTP_BAD_REQUEST);
            }

            $data = [
                'user_id'     => $user['id'],
                'name'        => $request['name'],
                'lo_rate'     => floatval($request['lo_rate']),
                'de_rate'     => floatval($request['de_rate']),
                'de_percent'  => floatval($request['de_percent']),
                'xien_rate'   => floatval($request['xien_rate']),
                'bacang_rate' => floatval($request['bacang_rate']),
            ];

            $customer = Customer::create($data);
            if ($customer) {
                return $this->sendResponse($customer, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * update
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        $customer = Customer::where('id', $id)->first();
        if (empty($customer)) {
            return $this->sendError('Customer not found !', Response::HTTP_NOT_FOUND);
        }
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:255',
            'lo_rate'     => 'required|numeric',
            'de_rate'     => 'required|numeric',
            'de_percent'  => 'required|numeric',
            'xien_rate'   => 'required|numeric',
            'bacang_rate' => 'required|numeric'
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = [
                'name'        => $request['name'],
                'lo_rate'     => floatval($request['lo_rate']),
                'de_rate'     => floatval($request['de_rate']),
                'de_percent'  => floatval($request['de_percent']),
                'xien_rate'   => floatval($request['xien_rate']),
                'bacang_rate' => floatval($request['bacang_rate']),
            ];

            $customer = Customer::where('id', $id)->limit(1)->update($data);
            if ($customer) {
                DB::commit();
                return $this->sendResponse($customer, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function getCustomerByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key' => 'required|size:6',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại!', Response::HTTP_BAD_REQUEST);
            }

            $customers = Customer::where('user_id', $user['id'])->get();
            if ($customers) {
                return $this->sendResponse($customers, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function getCustomerByCustomerDaily(Request $request)
    {
        try {
            $customerDaily = CustomerDaily::where('id', $request->customer_daily_id)->first();
            if (empty($customerDaily)) {
                return $this->sendError('CustomerDaily không tồn tại!', Response::HTTP_BAD_REQUEST);
            }
            $customer = Customer::where('id', $customerDaily->customer_id)->first();
            if ($customerDaily) {
                return $this->sendResponse($customer, Response::HTTP_OK);
            }
            return $this->sendError('Customer không tồn tại !', Response::HTTP_NOT_FOUND);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function checkCustomerExist($userId, $name){
        $customer = Customer::where('user_id', $userId)->where('name', $name)->first();
        if(empty($customer)){
            return false;
        }
        return true;
    }


}