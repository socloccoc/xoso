<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class UserApiController extends BaseApiController
{
    /**
     * Store
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:users,name',
            'type' => 'required|integer',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = [
                'name' => $request['name'],
                'type' => $request['type'],
                'key'  => $this->random_strings(6)
            ];
            $user = User::create($data);
            if ($user) {
                return $this->sendResponse($user, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Check user exist
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function checkUserExist(Request $request)
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
                return $this->sendError('User không tồn tại !', Response::HTTP_NOT_FOUND);
            }
            return $this->sendResponse($user, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    private function random_strings($length_of_string)
    {
        return substr(bin2hex(random_bytes($length_of_string)),
            0, $length_of_string);
    }

}