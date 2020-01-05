<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class TicketApiController extends BaseApiController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_daily_id' => 'required',
            'chuoi_so'          => 'required|max:255',
            'diem_tien'         => 'required|numeric',
            'type'              => 'required|integer',
            'fee'               => 'required|numeric',
            'profit'            => 'required|numeric',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        // lô và xiên(type: 0,2,3,4,5,6) từ 18h14 đến 18h41 sẽ không tạo đc
        $curentTime = Carbon::now()->format('H:i');
        if ($curentTime > '18:14' && $curentTime < '18:41') {
            if ($this->checkLoXien($request['type'])) {
                return $this->sendError('Lô và Xiên từ 18h14 đến 18h41 sẽ không tạo được!', Response::HTTP_BAD_REQUEST);
            }
        }

        // đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
        if ($curentTime > '18:26' && $curentTime < '18:41') {
            if ($this->checkDeVaBacang($request['type'])) {
                return $this->sendError('Đề và Ba Càng từ 18h26 đến 18h41 sẽ không tạo được!', Response::HTTP_BAD_REQUEST);
            }
        }

        // kiểm tra customer_daily có tồn tại hay không
        $cutomerDailyById = CustomerDaily::where('id', $request['customer_daily_id'])->first();
        if (empty($cutomerDailyById)) {
            return $this->sendError('Customer daily không tồn tại !', Response::HTTP_BAD_REQUEST);
        }
        try {
            $data = [
                'customer_daily_id' => $request['customer_daily_id'],
                'chuoi_so'          => $request['chuoi_so'],
                'diem_tien'         => $request['diem_tien'],
                'type'              => $request['type'],
                'fee'               => $request['fee'],
                'profit'            => $request['profit'],
            ];

            $ticket = Ticket::create($data);
            if ($ticket) {
                return $this->sendResponse($ticket, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * lấy dữ liệu theo 1 trong 2 trường customer_daily_id, Daily_date. ( truyền lên cái nào sẽ lấy theo cái đó)
     *
     */
    public function getTickets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key'          => 'required|size:10',
            'customer_daily_id' => 'required|integer',
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

            // lấy ra danh sách customer theo user
            $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
            if (empty($listCustomerByUser)) {
                return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
            }

            // danh sách customer_daily theo customer
            $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->pluck('id')->toArray();
            if (empty($listCustomerDaily)) {
                return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // kiểm tra xem customer_daily_id có khớp với user_key không
            if (!in_array($request['customer_daily_id'], $listCustomerDaily)) {
                return $this->sendError('customer_daily_id không khớp với user_key !', Response::HTTP_NOT_FOUND);
            }

            $tickets = Ticket::where('customer_daily_id', $request['customer_daily_id'])->get();
            return $this->sendResponse($tickets, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }

    }

    public function checkLoXien($type)
    {
        $types = [0, 2, 3, 4, 5, 6];
        if (in_array($type, $types)) {
            return true;
        }
        return false;
    }

    public function checkDeVaBacang($type)
    {
        $types = [1, 7];
        if (in_array($type, $types)) {
            return true;
        }
        return false;
    }
}