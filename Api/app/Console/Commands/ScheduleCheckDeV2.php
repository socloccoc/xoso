<?php

namespace App\Console\Commands;

use App\Helpers\Legend\CommonFunctions;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\SummaryResult;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckDeV2 extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkDeV2:start';

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

        // danh sách customer_daily theo customer
        $listCustomerDaily = CustomerDaily::where('daily_id', $daily['id'])->pluck('id')->toArray();

        if (empty($listCustomerDaily)) {
            $this->info('Customer_daily không tồn tại !');
            return false;
        }

        $des = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 1)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->orderBy('sum', 'desc')
            ->get();

        $bacangs = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 4)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->orderBy('sum', 'desc')
            ->get();

        $deMsg          = "<b>De. </b> \n";
        $bacangMsg      = "<b>Bacang. </b> \n";
        $deRecomMsg     = "<b>De. </b> \n";
        $bacangRecomMsg = "<b>Bacang. </b> \n";

        $decham     = $this->getMsgCham($des, 200000, $deMsg, $deRecomMsg, true);
        $bacangcham = $this->getMsgCham($bacangs, 20000, $bacangMsg, $bacangRecomMsg, false);

        $bacang = $this->getMsg($bacangs, 20000, $bacangMsg, $bacangRecomMsg, false);
        $de     = $this->getMsg($des, 200000, $deMsg, $deRecomMsg, true);

        $textCham = ""
            . (strlen($decham) > 15 ? $decham : '')
            . (strlen($bacangcham) > 20 ? $bacangcham : '');

        $text = ""
        . (strlen($de[0]) > 15 ? $de[0] : '')
        . (strlen($bacang[0]) > 20 ? $bacang[0] : '');

        $textRecom = ""
        . (strlen($de[1]) > 15 ? $de[1] : '')
        . (strlen($bacang[1]) > 20 ? $bacang[1] : '');

        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => "<b>Thông tin Đề Chạm ngày " . $currentDate . "</b>",
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
            'text'       => "<b>Khuyến nghị " . $currentDate . "</b>",
        ]);
        Telegram::sendMessage([
            'chat_id'    => '-1001466757473',
            'parse_mode' => 'HTML',
            'text'       => $textRecom,
        ]);
    }

    public function getMsgCham($data, $cross, $msg1, $msg2, $isDe = true) {
        $arrs = [];
        $deMin = $this->deMin();
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = preg_replace('/\s+/', '', $data[$i]['num']);
                if(count($data) == 1){
                    $arrs[$data[$i]['sum']] = $nums;
                } else {
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
            }
            foreach ($arrs as $ind => $arr) {
                if($isDe){
                    $n = floor(($ind) / 1000 / 10)*10;
                    $arr = $this->checkExist($deMin, $arr, true, true);
                    if(!empty($arr)){
                        $msg2 .= implode(',', $arr) . 'x' . $n . 'n.' . "\n";
                    }
                }else{
                    $m = ($ind) / 1000;
                    $arr = $this->checkExist($deMin, $arr, false, true);
                    if(!empty($arr)){
                        $msg2 .= implode(',', $arr) . 'x' . $m . 'n.' . "\n";
                    }
                }
            }
        }

        return $msg2;
    }

    public function getMsg($data, $cross, $msg1, $msg2, $isDe = true) {
        $deMin = $this->deMin();
        $arrs = [];
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = preg_replace('/\s+/', '', $data[$i]['num']);
                if(count($data) == 1){
                    $arrs[$data[$i]['sum']] = $nums;
                } else {
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
            }
            foreach ($arrs as $ind => $arr) {
                if ($ind >= $cross) {
                    $msg1 .= implode(',', $arr) . 'x' . $ind / 1000 . 'n.' . "\n";
                    if ($ind > $cross) {
                        if($isDe){
                            $n = floor(($ind - $cross) / 1000 / 10)*10;
                            if($n < 10){
                                $n = 10;
                            }
                            $arr = $this->checkExist($deMin, $arr, true);
                            if(!empty($arr)) {
                                $msg2 .= implode(',', $arr) . 'x' . $n . 'n.' . "\n";
                            }
                        }else{
                            $m = ($ind - $cross) / 1000;
                            if($m < 10){
                                $m = 10;
                            }
                            if(!empty($arr)) {
                                $arr = $this->checkExist($deMin, $arr);
                            }
                            $msg2 .= implode(',', $arr) . 'x' . $m . 'n.' . "\n";
                        }
                    }
                }
            }
        }

        return [$msg1, $msg2];
    }

    public function checkExist($demin, $arr, $isDe = false, $cham = false){
        $result = [];
        foreach ($arr as $it){
            $itB = $it;
            if(!$isDe) {
                $itB = substr($it, 1, 2);
            }
            $itC = CommonFunctions::convertToBinary($itB);
            if($cham){
                if (in_array($itC, $demin)) {
                    $result[] = $it;
                }
            }else{
                if (!in_array($itC, $demin)) {
                    $result[] = $it;
                }
            }

        }
        return $result;
    }

    public function deMin()
    {
        $resultYesterday = SummaryResult::orderBy('id', 'DESC')->first()->toArray();
        $nhi_1 = CommonFunctions::convertToBinary($resultYesterday['nhi_1']);
        $nhi_2 = CommonFunctions::convertToBinary($resultYesterday['nhi_2']);

        if (!in_array($nhi_1, ['01', '10', '00', '11']) || !in_array($nhi_2, ['01', '10', '00', '11'])) {
            return response()->json(['success' => false, 'msg' => __("There was errors, please try again !")]);
        }

        $results = SummaryResult::all();
        $binary = [];
        for ($i = 0; $i < count($results) - 1; $i++) {
            if ($nhi_1 == CommonFunctions::convertToBinary($results[$i]['nhi_1']) && $nhi_2 == CommonFunctions::convertToBinary($results[$i]['nhi_2'])) {
                $binary[$nhi_1 . $nhi_2][] = CommonFunctions::convertToBinary($results[$i]['dac_biet']);
            }
        }

        $pair = $nhi_1 . $nhi_2;

        $kq = array_count_values($binary[$pair]);
        $sum = array_sum($kq);
        $data = [
            '01' => $this->calculatePercent($kq['01'], $sum),
            '10' => $this->calculatePercent($kq['10'], $sum),
            '00' => $this->calculatePercent($kq['00'], $sum),
            '11' => $this->calculatePercent($kq['11'], $sum),
        ];

        return array_keys($data, min($data));

    }

    public function calculatePercent($num, $sum)
    {
        return number_format($num / $sum * 100, 1);
    }
}
