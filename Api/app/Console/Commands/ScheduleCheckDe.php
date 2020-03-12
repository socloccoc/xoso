<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckDe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkDe:start';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentDate = Carbon::now()->format('d-m-Y');
        $daily = Daily::where('date', $currentDate)->first();
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

        $deMsg = "<b>De. </b> \n";
        $bacangMsg = "<b>Bacang. </b> \n";
        $deRecomMsg = "<b>De. </b> \n";
        $bacangRecomMsg = "<b>Bacang. </b> \n";

//        foreach ($des as $point) {
//            if ($point['sum'] >= 200000) {
//                $deMsg .= $point['num'] . 'x' . $point['sum'] / 1000 . 'n.' . "\n";
//                if ($point['sum'] > 200000) {
//                    $deRecomMsg .= $point['num'] . 'x' . ($point['sum'] - 200000) / 1000 . 'n.' . "\n";
//                }
//            }
//        }
//
//
//        foreach ($bacangs as $point) {
//            if ($point['sum'] >= 20000) {
//                $bacangMsg .= $point['num'] . 'x' . $point['sum'] / 1000 . 'n.' . "\n";
//                if ($point['sum'] > 20000) {
//                    $bacangRecomMsg .= $point['num'] . 'x' . ($point['sum'] - 20000) / 1000 . 'n.' . "\n";
//                }
//            }
//        }
//
//        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
//            . (strlen($deMsg) > 15 ? $deMsg : '')
//            . (strlen($bacangMsg) > 20 ? $bacangMsg : '');
//
//        $textRecom = "<b>Khuyến nghị " . $currentDate . "</b>\n"
//            . (strlen($deRecomMsg) > 15 ? $deRecomMsg : '')
//            . (strlen($bacangRecomMsg) > 20 ? $bacangRecomMsg : '');

        $de = $this->getMsg($des, 200000, $deMsg, $deRecomMsg);
        $bacang = $this->getMsg($bacangs, 20000, $bacangMsg, $bacangRecomMsg);

        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
            . (strlen($de[0]) > 15 ? $de[0] : '')
            . (strlen($bacang[0]) > 20 ? $bacang[0] : '');

        $textRecom = "<b>Khuyến nghị " . $currentDate . "</b>\n"
            . (strlen($de[1]) > 15 ? $de[1] : '')
            . (strlen($bacang[1]) > 20 ? $bacang[1] : '');

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

    public function getMsg($data, $cross, $msg1, $msg2)
    {
        $arrs = [];
        if (!empty($data)) {
            for ($i = 0 ; $i < count($data) ; $i++) {
                $nums[] = $data[$i]['num'];
                if ($i < count($data) - 1) {
                    if ($data[$i]['sum'] != $data[$i + 1]['sum']) {
                        $arrs[$data[$i]['sum']] = $nums;
                        $nums = [];
                    }
                } else {
                    if ($data[$i]['sum'] == $data[$i - 1]['sum']) {
                        $arrs[$data[$i - 1]['sum']][] = $data[$i]['num'];
                    } else {
                        $arrs[$data[$i]['sum']] = $data[$i]['num'];
                    }
                }
            }
            foreach ($arrs as $ind => $arr) {
                if ($ind >= $cross) {
                    $msg1 .= implode(',', $arr) . 'x' . $ind / 1000 . 'n.' . "\n";
                    if ($ind > $cross) {
                        $msg2 .= implode(',', $arr) . 'x' . ($ind - $cross) / 1000 . 'n.' . "\n";
                    }
                }
            }
        }

        return [$msg1, $msg2];
    }
}
