<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckLo extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkLo:start';

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
//        foreach ($los as $point) {
        //            if ($point['sum'] >= 250) {
        //                $loMsg .= $point['num'] . 'x' . $point['sum'] . 'đ.' . "\n";
        //                if ($point['sum'] > 250) {
        //                    $loRecomMsg .= $point['num'] . 'x' . $point['sum'] - 250 . 'đ.' . "\n";
        //                }
        //            }
        //        }
        //
        //        foreach ($xiens as $point) {
        //            if ($point['sum'] >= 300000) {
        //                $xienMsg .= $point['num'] . 'x' . $point['sum'] / 1000 . 'n.' . "\n";
        //                if ($point['sum'] > 300000) {
        //                    $xienRecomMsg .= $point['num'] . 'x' . ($point['sum'] - 300000) / 1000 . 'n.' . "\n";
        //                }
        //            }
        //        }
        //
        //        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
        //        . (strlen($loMsg) > 15 ? $loMsg : '')
        //        . (strlen($xienMsg) > 15 ? $xienMsg : '');
        //
        //        $textRecom = "<b>Khuyến nghị " . $currentDate . "</b>\n"
        //        . (strlen($loRecomMsg) > 15 ? $loRecomMsg : '')
        //        . (strlen($xienRecomMsg) > 15 ? $xienRecomMsg : '');

        $xien = $this->getMsg($xiens, 300000, $xienMsg, $xienRecomMsg);
        $lo   = $this->getMsg($los, 200, $loMsg, $loRecomMsg, true);

        $text = ""
        . (strlen($lo[0]) > 15 ? $lo[0] : '')
        . (strlen($xien[0]) > 15 ? str_replace('-', ' ', $xien[0]) : '');

        $textRecom = ""
        . (strlen($lo[1]) > 15 ? $lo[1] : '')
        . (strlen($xien[1]) > 15 ? str_replace('-', ' ', $xien[1]) : '');

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>",
        ]);

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => "<b>Khuyến nghị " . $currentDate . "</b>"
        ]);

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $textRecom,
        ]);

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
                        $arrs[$data[$i - 1]['sum']][] = $data[$i]['num'];
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
            foreach ($arrs as $ind => $arr) {
                $divisor = $islo ? 1 : 1000;
                $unit    = $islo ? 'd.' : 'n.';
                if ($ind >= $cross && $islo) {
                    $msg1 .= implode(",", $arr) . 'x' . $ind / $divisor . $unit . "\n";
                    if ($ind > $cross) {
                        $k = ($ind - $cross) / $divisor > 10 ? ($ind - $cross) / $divisor : 10;
                        $msg2 .= implode(",", $arr) . 'x' . $k . $unit . "\n";
                    }
                }

                if (!$islo) {
                    foreach ($arr as $item){
                        $item = trim($item);
                        if(substr_count($item, '-') == 1 && $ind >= $cross){
                            $xi2 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            if ($ind > $cross) {
                                $n = ($ind - $cross) / $divisor > 10 ? ($ind - $cross) / $divisor : 10;
                                $xi2kn .= $item . 'x' . $n . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 2 && $ind >= 100000){
                            $xi3 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            $cross_x3 = 100000;
                            if ($ind > $cross_x3) {
                                $m = ($ind - $cross_x3) / $divisor > 10 ? ($ind - $cross_x3) / $divisor : 10;
                                $xi3kn .= $item . 'x' . $m . $unit . "\n";
                            }
                        }

                        if(substr_count($item, '-') == 3 && $ind >= 50000){
                            $xi4 .= $item . 'x' . $ind / $divisor . $unit . "\n";
                            $cross_x4 = 50000;
                            if ($ind > $cross_x4) {
                                $l = ($ind - $cross_x4) / $divisor > 10 ? ($ind - $cross_x4) / $divisor : 10;
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
}
