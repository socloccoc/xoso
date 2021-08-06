<?php

namespace App\Console\Commands;

use App\Models\CrossSetting;
use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckLo extends Command
{
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

        $loMsg = "<b>Lo.</b> \n";
        $xienMsg = "";
        $loRecomMsg = "<b>Lo.</b> \n";
        $xienRecomMsg = "";

        $xien = $this->getMsg($xiens, 300000, $xienMsg, $xienRecomMsg);
        $lo = $this->getMsg($los, 200, $loMsg, $loRecomMsg, true);

        $textRecom = ""
            . (strlen($lo[1]) > 15 ? $lo[1] : '')
            . (strlen($xien[1]) > 15 ? str_replace('-', '-', $xien[1]) : '');

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

    public function getMsg($data, $cross, $msg1, $msg2, $islo = false)
    {
        $arrs = [];
        $crossSetting = CrossSetting::where('id', 2)->first();
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = $data[$i]['num'];
                if ($i < count($data) - 1) {
                    if ($data[$i]['sum'] != $data[$i + 1]['sum']) {
                        $arrs[$data[$i]['sum']] = $nums;
                        $nums = [];
                    }
                } else {
                    if ($data[$i]['sum'] == $data[$i - 1]['sum']) {
                        $arrs[$data[$i - 1]['sum']] = $nums;
                    } else {
                        $arrs[$data[$i]['sum']][] = $data[$i]['num'];
                    }
                }
            }

            $xi2kn = '<b>Xien2.</b>' . "\n";
            $xi3kn = '<b>Xien3.</b>' . "\n";
            $xi4kn = '<b>Xien4.</b>' . "\n";
            foreach ($arrs as $ind => $arr) {
                $divisor = $islo ? 1 : 1000;
                $unit = $islo ? 'd.' : 'n.';
                if ($islo) {
                    $cross = $crossSetting['lo'];
                    if ($ind > $cross) {
                        $k = ($ind - $cross) / $divisor > 10 ? ($ind - $cross) / $divisor : 10;
                        $msg2 .= implode(",", $arr) . 'x' . $k . $unit . "\n";
                    }
                }

                if (!$islo) {
                    foreach ($arr as $item) {
                        $item = trim($item);
                        if (substr_count($item, '-') == 1) {
                            $cross = $crossSetting['xien2'];
                            if ($ind > $cross) {
                                $n = ($ind - $cross) / $divisor > 10 ? ($ind - $cross) / $divisor : 10;
                                $xi2kn .= $item . 'x' . $n . $unit . "\n";
                            }
                        }

                        if (substr_count($item, '-') == 2) {
                            $cross = $crossSetting['xien3'];
                            $cross_x3 = $cross;
                            if ($ind > $cross_x3) {
                                $m = ($ind - $cross_x3) / $divisor > 10 ? ($ind - $cross_x3) / $divisor : 10;
                                $xi3kn .= $item . 'x' . $m . $unit . "\n";
                            }

                        }

                        if (substr_count($item, '-') == 3) {
                            $cross = $crossSetting['xien4'];
                            $cross_x4 = $cross;
                            if ($ind > $cross_x4) {
                                $l = ($ind - $cross_x4) / $divisor > 10 ? ($ind - $cross_x4) / $divisor : 10;
                                $xi4kn .= $item . 'x' . $l . $unit . "\n";
                            }

                        }

                    }

                }

            }
            if (!$islo) {
                if (strlen($xi2kn) > 15) {
                    $msg2 .= $xi2kn;
                }

                if (strlen($xi3kn) > 15) {
                    $msg2 .= $xi3kn;
                }

                if (strlen($xi4kn) > 15) {
                    $msg2 .= $xi4kn;
                }
            }
        }

        return [$msg1, $msg2];
    }
}
