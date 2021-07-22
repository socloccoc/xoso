<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\SummaryResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckLoV2 extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkLov2:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $currentDate = Carbon::now()->format('d-m-Y');
        $daily       = Daily::where('date', $currentDate)->first();
        if (empty($daily)) {
            $this->info('Daily không tồn tại !');
            return false;
        }

        $userTest = User::where('key', '444888')->first();
        $customerTest = Customer::where('user_id', $userTest['id'])->pluck('id')->toArray();

        // danh sách customer_daily theo customer
        $listCustomerDaily = CustomerDaily::where('daily_id', $daily['id'])->whereNotIn('customer_id', $customerTest)->pluck('id')->toArray();

        if (empty($listCustomerDaily)) {
            $this->info('Customer_daily không tồn tại !');
            return false;
        }

        $los = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 0)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->orderBy('sum', 'desc')
            ->get();
        $xiens = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->whereIn('type', [2, 3])
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->orderBy('sum', 'desc')
            ->get();
        $loMsg        = "<b>Lo.</b> \n";
        $xienMsg      = "";
        $loRecomMsg   = "<b>Lo.</b> \n";
        $xienRecomMsg = "";

        $xiemCham   = $this->getMsgCham($xiens, 300000, $xienMsg, $xienRecomMsg);
        $locham   = $this->getMsgCham($los, 200, $loMsg, $loRecomMsg, true);

        $xien = $this->getMsg($xiens, 300000, $xienMsg, $xienRecomMsg);
        $lo   = $this->getMsg($los, 200, $loMsg, $loRecomMsg, true);

        $textCham = ""
            . (strlen($locham) > 15 ? $locham : '')
            . (strlen($xiemCham) > 15 ? str_replace('-', ' ', $xiemCham) : '');

        $text = ""
        . (strlen($lo[0]) > 15 ? $lo[0] : '')
        . (strlen($xien[0]) > 15 ? str_replace('-', ' ', $xien[0]) : '');

        $textRecom = ""
        . (strlen($lo[1]) > 15 ? $lo[1] : '')
        . (strlen($xien[1]) > 15 ? str_replace('-', ' ', $xien[1]) : '');

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => "<b>Thông tin Lô Chạm ngày " . $currentDate . "</b>",
        ]);

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => $textCham,
        ]);

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>",
        ]);

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => "<b>Khuyến nghị " . $currentDate . "</b>"
        ]);

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => $textRecom,
        ]);

    }

    public function getMsgCham($data, $cross, $msg1, $msg2, $islo = false) {
        $arrs = [];
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = $data[$i]['num'];
                if ($i < count($data) - 1) {
                    if ($data[$i]['sum'] != $data[$i + 1]['sum']) {
                        $arrs[$data[$i]['sum']] = $nums;
                        $nums                   = [];
                    }
                } else {
                    if ($data[$i]['sum'] == $data[$i - 1]['sum']) {
                        $arrs[$data[$i - 1]['sum']] = $nums;
                    } else {
                        $arrs[$data[$i]['sum']][] = $data[$i]['num'];
                    }
                }
            }

            $xi2kn = '<b>Xi2.</b>'."\n";
            $xi3kn = '<b>Xi3.</b>'."\n";
            $xi4kn = '<b>Xi4.</b>'."\n";
            $aList = $this->Alist();

            foreach ($arrs as $ind => $arr) {

                $divisor = $islo ? 1 : 1000;
                $unit    = $islo ? 'd.' : 'n.';
                if ($islo) {

                    $k = $ind / $divisor;

                    // kiểm tra xem có ở trong A list hay không
                    $arr_new = [];
                    foreach ($arr as $it){
                        if(in_array($it, $aList)){
                            $arr_new[] = $it;
                        }
                    }

                    if(!empty($arr_new)){
                        $msg2 .= implode(",", $arr_new) . 'x' . $k . $unit . "\n";
                    }

                }

                if (!$islo) {

                    foreach ($arr as $item){
                        $item = trim($item);
                        $check = $this->checkExist($aList, $item);
                        if(substr_count($item, '-') == 1){
                            if ($check) {
                                $n = ($ind) / $divisor;
                                $xi2kn .= $item . 'x' . $n . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 2){
                            if ($check) {
                                $m = ($ind) / $divisor;
                                $xi3kn .= $item . 'x' . $m . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 3){
                            if ($check) {
                                $l = ($ind) / $divisor;
                                $xi4kn .= $item . 'x' . $l . $unit . "\n";
                            }
                        }

                    }

                }

            }
            if(!$islo) {
                if(strlen($xi2kn) > 15){
                    $msg2 .= $xi2kn;
                }

                if(strlen($xi3kn) > 15){
                    $msg2 .= $xi3kn;
                }

                if(strlen($xi4kn) > 15){
                    $msg2 .= $xi4kn;
                }
            }
        }

        return $msg2;
    }


    public function getMsg($data, $cross, $msg1, $msg2, $islo = false) {
        $arrs = [];
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = $data[$i]['num'];
                if ($i < count($data) - 1) {
                    if ($data[$i]['sum'] != $data[$i + 1]['sum']) {
                        $arrs[$data[$i]['sum']] = $nums;
                        $nums                   = [];
                    }
                } else {
                    if ($data[$i]['sum'] == $data[$i - 1]['sum']) {
                        $arrs[$data[$i - 1]['sum']] = $nums;
                    } else {
                        $arrs[$data[$i]['sum']][] = $data[$i]['num'];
                    }
                }
            }

            $xi2 = '<b>Xi2.</b>'."\n";
            $xi3 = '<b>Xi3.</b>'."\n";
            $xi4 = '<b>Xi4.</b>'."\n";

            $xi2kn = '<b>Xi2.</b>'."\n";
            $xi3kn = '<b>Xi3.</b>'."\n";
            $xi4kn = '<b>Xi4.</b>'."\n";
            $aList = $this->Alist();

            foreach ($arrs as $ind => $arr) {

                $divisor = $islo ? 1 : 1000;
                $unit    = $islo ? 'd.' : 'n.';
                if ($islo) {
                    if($ind >= $cross){
                        $msg1 .= implode(",", $arr) . 'x' . $ind / $divisor . $unit . "\n";
                    }

                    $k = $ind / $divisor;

                    if($k < 10) continue;

                    // kiểm tra xem có ở trong A list hay không
                    $arr_new = [];
                    $arr_new_2 = [];
                    foreach ($arr as $it){
                        if(!in_array($it, $aList)){
                            $arr_new[] = $it;
                        }else{
                            if($ind > 600){
                                $k2 = ($ind - 600) / $divisor > 10 ? ($ind-600) / $divisor : 10;
                                $arr_new_2[] = $it;
                            }
                        }
                    }

                    if(!empty($arr_new)){
                        $msg2 .= implode(",", $arr_new) . 'x' . $k . $unit . "\n";
                    }

                    if(!empty($arr_new_2)){
                        $msg2 .= implode(",", $arr_new_2) . 'x' . $k2 . $unit . "\n";
                    }

                }

                if (!$islo) {

                    foreach ($arr as $item){
                        $item = trim($item);
                        $check = $this->checkExist($aList, $item);
                        if(substr_count($item, '-') == 1 && $ind >= $cross){
                            $xi2 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            if (!$check) {
                                $n = ($ind) / $divisor > 10 ? ($ind - $cross) / $divisor : 10;
                                $xi2kn .= $item . 'x' . $n . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 2 && $ind >= 100000){
                            $xi3 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            $cross_x3 = 100000;
                            if (!$check) {
                                $m = ($ind) / $divisor > 10 ? ($ind) / $divisor : 10;
                                $xi3kn .= $item . 'x' . $m . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 3 && $ind >= 50000){
                            $xi4 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            $cross_x4 = 50000;
                            if (!$check) {
                                $l = ($ind) / $divisor > 10 ? ($ind) / $divisor : 10;
                                $xi4kn .= $item . 'x' . $l . $unit . "\n";
                            }
                        }

                    }

                }

            }
            if(!$islo) {
                if(strlen($xi2) > 15){
                    $msg1 .= $xi2;
                }
                if(strlen($xi3) > 15){
                    $msg1 .= $xi3;
                }
                if(strlen($xi4) > 15){
                    $msg1 .= $xi4;
                }
                ///////
                if(strlen($xi2kn) > 15){
                    $msg2 .= $xi2kn;
                }

                if(strlen($xi3kn) > 15){
                    $msg2 .= $xi3kn;
                }

                if(strlen($xi4kn) > 15){
                    $msg2 .= $xi4kn;
                }
            }
        }

        return [$msg1, $msg2];
    }

    public function checkExist($alist, $arr){
        $arr = explode('-', $arr);
        foreach ($arr as $it){
            if(in_array($it, $alist)){
                return true;
            }
        }
        return false;
    }

    public function Alist()
    {
        // lấy những số mà 6 ngày chưa về
        $results = SummaryResult::orderBy('id', 'desc')->select('dac_biet', 'nhat', 'nhi_1', 'nhi_2', 'ba_1', 'ba_2', 'ba_3', 'ba_4', 'ba_5', 'ba_6',
            'tu_1', 'tu_2', 'tu_3', 'tu_4', 'nam_1', 'nam_2', 'nam_3', 'nam_4', 'nam_5', 'nam_6', 'sau_1', 'sau_2', 'sau_3', 'bay_1', 'bay_2', 'bay_3', 'bay_4')
            ->skip(0)->take(6)->get()->toArray();
        $arr = [];
        foreach ($results as $result) {
            $a = array_values($result);
            $arr = array_unique(array_merge($arr, $a));
        }

        $arr2 = [];
        for ($i = 0; $i < 100; $i++) {
            $j = $i;
            if ($i < 10) {
                $j = '0' . $i;
            }
            $arr2[] = (string)$j;
        }

        $rl = [];
        foreach ($arr2 as $item) {
            if (!in_array($item, $arr)) {
                $rl[] = $item;
            }
        }

        return $rl;

    }
}
