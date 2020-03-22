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
        $xienMsg      = "<b>Xien.</b> \n";
        $loRecomMsg   = "<b>Lo.</b> \n";
        $xienRecomMsg = "<b>Xien.</b> \n";
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

        $lo   = $this->getMsg($los, 250, $loMsg, $loRecomMsg, true);
        $xien = $this->getMsg($xiens, 300000, $xienMsg, $xienRecomMsg);

        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
        . (strlen($lo[0]) > 15 ? $lo[0] : '')
        . (strlen($xien[0]) > 15 ? $xien[0] : '');

        $textRecom = "<b>Khuyến nghị " . $currentDate . "</b>\n"
        . (strlen($lo[1]) > 15 ? $lo[1] : '')
        . (strlen($xien[1]) > 15 ? $xien[1] : '');

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
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
            foreach ($arrs as $ind => $arr) {
                if ($ind >= $cross) {
                    $divisor = $islo ? 1 : 1000;
                    $unit    = $islo ? 'd.' : 'n.';
                    $msg1 .= implode(',', $arr) . 'x' . $ind / $divisor . $unit . "\n";
                    if ($ind > $cross) {
                        $msg2 .= implode(',', $arr) . 'x' . ($ind - $cross) / $divisor . $unit . "\n";
                    }
                }
            }
        }

        return [$msg1, $msg2];
    }
}
