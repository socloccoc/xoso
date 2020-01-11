<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class TicketApiController extends BaseApiController
{
    /**
     * Lô và xiên(type: 0,2,3,4,5,6) từ 18h14 đến 18h41 sẽ không tạo đc, đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
     * Và update lại money in của customer_daily_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_daily_id' => 'required',
            'chuoi_so'          => 'required|max:255',
            'diem_tien'         => 'required|numeric',
            'type'              => 'required|integer',
            'fee'               => 'required|numeric',
            'sales'             => 'required|numeric',
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
                'sales'             => $request['sales'],
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
     * Lô và xiên(type: 0,2,3,4,5,6) từ 18h14 đến 18h41 sẽ không tạo đc, đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
     * update lại money in của customer_daily_id
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::where('id', $id)->first();
        if (empty($ticket)) {
            $this->sendError('Ticket không tồn tại !', Response::HTTP_NOT_FOUND);
        }
        $validator = Validator::make($request->all(), [
            'chuoi_so'  => 'required|max:255',
            'diem_tien' => 'required|numeric',
            'type'      => 'required|integer',
            'fee'       => 'required|numeric',
            'sales'     => 'required|numeric',
            'profit'    => 'required|numeric',
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

        try {
            $data = [
                'chuoi_so'  => $request['chuoi_so'],
                'diem_tien' => $request['diem_tien'],
                'type'      => $request['type'],
                'fee'       => $request['fee'],
                'sales'     => $request['sales'],
                'profit'    => $request['profit'],
            ];

            $ticket = Ticket::where('id', $id)->limit(1)->update($data);
            if ($ticket) {
                return $this->sendResponse($ticket, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Ticket chỉ xoá trước 18h14 với type(0,2,3,4,5,6) và 18h26 với type(1,7) trong cùng ngày
     * và update lại money in của customer_daily_id. sau thời gian đó không thể xoá
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $ticket = Ticket::where('id', $id)->first();
            if (empty($ticket)) {
                $this->sendError('Ticket không tồn tại !', Response::HTTP_NOT_FOUND);
            }
            // lô và xiên(type: 0,2,3,4,5,6) chỉ xóa được trước 18h14 cùng ngày
            $curentDate = Carbon::now()->format('Y-m-d');
            $curentTime = Carbon::now()->format('H:i');
            if (substr($ticket['updated_at'], 0, 10) != $curentDate) {
                return $this->sendError('Ticket đã quá hạn, không thể xóa !', Response::HTTP_BAD_REQUEST);
            }

            if ($curentTime < '18:14') {
                if ($this->checkLoXien($ticket['type'])) {
                    return $this->sendError('Lô và Xiên chỉ được xóa trước 18h14 cùng ngày !', Response::HTTP_BAD_REQUEST);
                }
            }

            // đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
            if ($curentTime < '18:26') {
                if ($this->checkDeVaBacang($ticket['type'])) {
                    return $this->sendError('Đề và Ba Càng chỉ được xóa trước 18h26 cùng ngày!', Response::HTTP_BAD_REQUEST);
                }
            }
            $ticket = Ticket::where('id', $id)->delete();
            return $this->sendResponse($ticket, Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Lấy dữ liệu theo 1 trong 2 trường customer_daily_id, Daily_date. ( truyền lên cái nào sẽ lấy theo cái đó)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getTickets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key'          => 'required|size:6',
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

    public function summaryOfResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key'   => 'required|size:6',
            'daily_date' => 'required',
        ], []);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $daily = Daily::where('date', $request['daily_date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // kiểm tra user có tồn tại hay không
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            if ($user['type'] == 1) {
                // lấy ra danh sách customer theo user
                $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
                if (empty($listCustomerByUser)) {
                    return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
                }

                // danh sách customer_daily theo customer
                $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->where('daily_id', $daily['id'])->pluck('id')->toArray();
                if (empty($listCustomerDaily)) {
                    return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
                }

                $tickets = Ticket::whereIn('customer_daily_id', $listCustomerDaily)->groupBy('type')
                    ->selectRaw('type, sum(fee) as thuc_thu, sum(sales) as doanh_so, sum(win_num) as so_luong_trung, sum(profit) as tien_trung')
                    ->get();
                if (empty($tickets)) {
                    return $this->sendError('Ticket không tồn tại !', Response::HTTP_NOT_FOUND);
                }
                $data = [];
                foreach ($tickets as $ticket) {
                    $ticket['date'] = $request['daily_date'];
                    $ticket['loi_nhuan'] = $ticket['thuc_thu'] - $ticket['tien_trung'];
                    $data[] = $ticket;
                }
                return $this->sendResponse($data, Response::HTTP_OK);
            } else {
                $users = User::all();
                $response = [];
                foreach ($users as $user) {
                    // lấy ra danh sách customer theo user
                    $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
                    if (empty($listCustomerByUser)) {
                        return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
                    }

                    // danh sách customer_daily theo customer
                    $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->where('daily_id', $daily['id'])->pluck('id')->toArray();
                    if (empty($listCustomerDaily)) {
                        return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
                    }

                    $tickets = Ticket::whereIn('customer_daily_id', $listCustomerDaily)->groupBy('type')
                        ->selectRaw('type, sum(fee) as thuc_thu, sum(sales) as doanh_so, sum(win_num) as so_luong_trung, sum(profit) as tien_trung')
                        ->get();
                    if (empty($tickets)) {
                        return $this->sendError('Ticket không tồn tại !', Response::HTTP_NOT_FOUND);
                    }
                    $data = [];
                    foreach ($tickets as $ticket) {
                        $ticket['date'] = $request['daily_date'];
                        $ticket['loi_nhuan'] = $ticket['thuc_thu'] - $ticket['tien_trung'];
                        $data[] = $ticket;
                    }
                    $response[$user['name']] = $data;
                }
                return $this->sendResponse($response, Response::HTTP_OK);
            }
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