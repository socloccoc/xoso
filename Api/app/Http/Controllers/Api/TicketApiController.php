<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use drupol\phpermutations\Generators\Combinations;
use App\Helpers\Legend\CommonFunctions;

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
        if ($curentTime > '18:14' && $curentTime < '19:10') {
            if ($this->checkLoXien($request['type'])) {
                return $this->sendError('Lô và Xiên từ 18h14 đến 18h41 sẽ không tạo được!', Response::HTTP_BAD_REQUEST);
            }
        }

        // đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
        if ($curentTime > '18:26' && $curentTime < '19:10') {
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
            DB::beginTransaction();
            $ticket = Ticket::create($data);
            if ($ticket) {
                $this->updateMoneyIn($request['customer_daily_id'], $request['fee']);
                $this->updatePoint($ticket, $ticket['diem_tien'], false);
                DB::commit();
                return $this->sendResponse($ticket, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function updatePoint($ticket, $diemTien, $sub = false)
    {
        try {
            $arrs = [];

            // lô hoặc đề
            if ($ticket['type'] == 1 || $ticket['type'] == 0) {
                $arrs = $this->breakStringNumber($ticket['chuoi_so']);
            }

            // xiên hoặc ba càng
            if ($ticket['type'] == 2 || $ticket['type'] == 4) {
                $arrs = explode(',', $ticket['chuoi_so']);

            }

            // xiên quay
            if ($ticket['type'] == 3) {
                $arrs = $this->combinations($ticket['chuoi_so']);
            }
            if (!empty($arrs)) {
                DB::beginTransaction();
                foreach ($arrs as $arr) {
                    if ($ticket['type'] == 3) {
                        $arr = implode('-', $arr);
                    }
                    $point = Point::where('customer_daily_id', $ticket['customer_daily_id'])->where('num', $arr)->where('type', $ticket['type'])->first();
                    if (empty($point)) {
                        Point::create(['customer_daily_id' => $ticket['customer_daily_id'], 'num' => $arr, 'diem_tien' => $diemTien, 'type' => $ticket['type']]);
                    } else {
                        if ($sub) {
                            $diemTienNew = $point['diem_tien'] - $diemTien;
                        } else {
                            $diemTienNew = $point['diem_tien'] + $diemTien;
                        }
                        $point->update(['diem_tien' => $diemTienNew]);
                    }
                }
                DB::commit();
            }
        } catch (\Exception $ex) {
            DB::rollBack();
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
        try {
            DB::beginTransaction();
            $ticket = Ticket::where('id', $id)->where('status', 0)->first();
            if (empty($ticket)) {
                $this->sendError('Ticket không tồn tại hoặc đã hết hạn !', Response::HTTP_NOT_FOUND);
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
            if ($curentTime > '18:14' && $curentTime < '19:10') {
                if ($this->checkLoXien($request['type'])) {
                    return $this->sendError('Lô và Xiên từ 18h14 đến 18h41 sẽ không thể cập nhật!', Response::HTTP_BAD_REQUEST);
                }
            }

            // đề và ba càng (type: 1, 7)  từ 18h26 đến 18h41 sẽ ko tạo được
            if ($curentTime > '18:26' && $curentTime < '19:10') {
                if ($this->checkDeVaBacang($request['type'])) {
                    return $this->sendError('Đề và Ba Càng từ 18h26 đến 18h41 sẽ không thể cập nhật!', Response::HTTP_BAD_REQUEST);
                }
            }

            $data = [
                'chuoi_so'  => $request['chuoi_so'],
                'diem_tien' => $request['diem_tien'],
                'type'      => $request['type'],
                'fee'       => $request['fee'],
                'sales'     => $request['sales'],
                'profit'    => $request['profit'],
            ];

            $this->updateMoneyIn($ticket['customer_daily_id'], $request['fee'] - $ticket['fee']);

            $this->updatePoint($ticket, $ticket['diem_tien'], true);

            $ticket = Ticket::where('id', $id)->limit(1)->update($data);
            if ($ticket) {
                $ticket = Ticket::where('id', $id)->where('status', 0)->first();
                $this->updatePoint($ticket, $ticket['diem_tien'], false);
                DB::commit();
                return $this->sendResponse($ticket, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
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
            DB::beginTransaction();
            $ticket = Ticket::where('id', $id)->where('status', 0)->first();
            if (empty($ticket)) {
                $this->sendError('Ticket không tồn tại hoặc đã hết hạn !', Response::HTTP_NOT_FOUND);
            }
            $daily = $this->getDailyByCustomerDaily($ticket['customer_daily_id']);
            // lô và xiên(type: 0,2,3,4,5,6) chỉ xóa được trước 18h14 cùng ngày
            $curentDate = Carbon::now()->format('Y-m-d');
            $curentTime = Carbon::now()->format('H:i');

            if ($curentDate == $daily['date']) {
                if ($curentTime > '18:14') {
                    if ($this->checkLoXien($ticket['type'])) {
                        return $this->sendError('Lô và Xiên chỉ được xóa trước 18h14 cùng ngày !', Response::HTTP_BAD_REQUEST);
                    }
                }

                if ($curentTime > '18:26') {
                    if ($this->checkDeVaBacang($ticket['type'])) {
                        return $this->sendError('Đề và Ba Càng chỉ được xóa trước 18h26 cùng ngày!', Response::HTTP_BAD_REQUEST);
                    }
                }
            }
            $this->updatePoint($ticket, $ticket['diem_tien'], true);
            $ticketRemove = Ticket::where('id', $id)->delete();
            if ($ticketRemove) {
                $this->updateMoneyIn($ticket['customer_daily_id'], -$ticket['fee']);
                DB::commit();
                return $this->sendResponse($ticketRemove, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
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

    public function getTicketByParam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key' => 'required|size:6',
            'type'     => 'integer',
            'date'     => 'required',
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

            $daily = Daily::where('date', $request['date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // lấy ra danh sách customer theo user
            $listCustomerByUser = Customer::where(function ($q) use ($user) {
                if ($user['type'] == 1) {
                    $q->where('user_id', $user['id']);
                }
            })->pluck('id')->toArray();

            if (empty($listCustomerByUser)) {
                return $this->sendError('Không tìm thấy khách hàng !', Response::HTTP_NOT_FOUND);
            }

            // danh sách customer_daily theo customer
            $listCustomerDaily = CustomerDaily::whereIn('customer_id', $listCustomerByUser)->where('daily_id', $daily['id'])->pluck('id')->toArray();
            if (empty($listCustomerDaily)) {
                return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            $tickets = Ticket::whereIn('customer_daily_id', $listCustomerDaily)->where('type', $request['type'])->get();
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
            DB::beginTransaction();
            $daily = Daily::where('date', $request['daily_date'])->first();
            if (empty($daily)) {
                return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // kiểm tra user có tồn tại hay không
            $user = User::where('key', $request['user_key'])->first();
            if (empty($user)) {
                return $this->sendError('User không tồn tại !', Response::HTTP_NOT_FOUND);
            }

            // lấy ra danh sách customer theo user
            $listCustomerByUser = Customer::where(function ($q) use ($user) {
                if ($user['type'] == 1) {
                    $q->where('user_id', $user['id']);
                }
            })->pluck('id')->toArray();

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
            DB::commit();
            return $this->sendResponse($data, Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function updateMoneyIn($customerDailyId, $money)
    {
        $customerDaily = CustomerDaily::where('id', $customerDailyId)->first();
        $moneyIn = $customerDaily['money_in'] + $money;
        $customerDaily->update(['money_in' => $moneyIn]);
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

    public function getDailyByCustomerDaily($customerDailyId)
    {
        $customerDaily = CustomerDaily::where('id', $customerDailyId)->first();
        $daily = Daily::where('id', $customerDaily['id'])->first();
        return $daily;
    }

    public function breakStringNumber($str)
    {
        $str = explode(',', $str);
        $result = [];
        foreach ($str as $item) {
            $item = strtolower($item);
            if (strpos($item, 'dau') !== false) {
                $result = array_merge($result, CommonFunctions::dauX($item));
            } elseif
            (strpos($item, 'dit') !== false) {
                $result = array_merge($result, CommonFunctions::ditX($item));
            } elseif (strpos($item, 'bo') !== false) {
                $result = array_merge($result, CommonFunctions::boXY($item));
            } elseif (strpos($item, 'tong') !== false) {
                $result = array_merge($result, CommonFunctions::tongX($item));
            } elseif (strpos($item, 'kepbang') !== false) {
                $result = array_merge($result, CommonFunctions::kepBang());
            } elseif (strpos($item, 'keplech') !== false) {
                $result = array_merge($result, CommonFunctions::kepLech());
            } elseif (strpos($item, 'cham') !== false) {
                $result = array_merge($result, CommonFunctions::chamX($item));
            } elseif (strlen($item) == 3 && is_numeric($item)) {
                $result = array_merge($result, [substr($item, 0, 2), substr($item, -2)]);
            } else {
                $result = array_merge($result, [$item]);
            }
        }
        return $result;
    }

    public function combinations($str)
    {
        $result = [];
        $arrs = explode(',', $str);
        foreach ($arrs as $arr) {
            $ep = explode('-', $arr);
            if (count($ep) == 3) {
                $com = new Combinations($ep, count($ep) - 1);
                $result = array_merge($result, $com->toArray());
            }
            if (count($ep) == 4) {
                $com = new Combinations($ep, count($ep) - 1);
                $result = array_merge($result, $com->toArray());
                $com2 = new Combinations($ep, count($ep) - 2);
                $result = array_merge($result, $com2->toArray());
            }
            $result[] = $ep;
        }
        return $result;
    }
}