<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class CustomerDailyApiController extends BaseApiController
{
    /**
     * Index
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Store
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'    => 'required',
            'name'        => 'required|max:255|unique:customer_dailies,name',
            'lo_rate'     => 'required',
            'de_rate'     => 'required',
            'de_percent'  => 'required',
            'xien_rate'   => 'required',
            'bacang_rate' => 'required'
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('Username đã tồn tại!', Response::HTTP_BAD_REQUEST);
            }

//            $customerExist = Customer::where('user_id', $user['id'])->first();
//            if (!empty($customerExist)) {
//                return $this->sendError('User_key đã được sử dụng!', Response::HTTP_BAD_REQUEST);
//            }

            $data = [
                'user_id'     => $user['id'],
                'name'        => $request['name'],
                'lo_rate'     => $request['lo_rate'],
                'de_rate'     => $request['de_rate'],
                'de_percent'  => $request['de_percent'],
                'xien_rate'   => $request['xien_rate'],
                'bacang_rate' => $request['bacang_rate'],
            ];

            $customer = Customer::create($data);
            if ($customer) {
                return $this->sendResponse($customer, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function getCustomerByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key' => 'required|size:10',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('Username đã tồn tại!', Response::HTTP_BAD_REQUEST);
            }

            $customers = Customer::where('user_id', $user['id'])->get();
            if ($customers) {
                return $this->sendResponse($customers, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Update
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Destroy
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

}