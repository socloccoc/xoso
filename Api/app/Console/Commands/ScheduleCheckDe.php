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

        $userTest = User::where('key', '444888')->first();
        $customerTest = Customer::where('user_id', $userTest['id'])->pluck('id')->toArray();

        // danh sách customer_daily theo customer
        $listCustomerDaily = CustomerDaily::where('daily_id', $daily['id'])->whereNotIn('customer_id', $customerTest)->pluck('id')->toArray();

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

        $de = $this->getMsg($des, 200000, $deMsg, $deRecomMsg, true);
        $bacang = $this->getMsg($bacangs, 20000, $bacangMsg, $bacangRecomMsg, false);

        $textRecom = ""
            . (strlen($de[1]) > 15 ? $de[1] : '')
            . (strlen($bacang[1]) > 20 ? $bacang[1] : '');

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => "<b>Khuyến nghị " . $currentDate . "</b>",
        ]);
        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $textRecom,
        ]);
    }

    public function getMsg($data, $cross, $msg1, $msg2, $isDe = true)
    {
        $arrs = [];
        $crossSetting = CrossSetting::where('id', 2)->first();
        if ($isDe) {
            $cross = $crossSetting['de'];
        } else {
            $cross = $crossSetting['bacang'];
        }
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $nums[] = preg_replace('/\s+/', '', $data[$i]['num']);
                if (count($data) == 1) {
                    $arrs[$data[$i]['sum']] = $nums;
                } else {
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
            }
            foreach ($arrs as $ind => $arr) {
                if ($ind > $cross) {
                    if ($isDe) {
                        $n = floor(($ind - $cross) / 1000 / 10) * 10;
                        if ($n < 10) {
                            $n = 10;
                        }
                        $msg2 .= implode(',', $arr) . 'x' . $n . 'n.' . "\n";
                    } else {
                        $m = ($ind - $cross) / 1000;
                        if ($m < 10) {
                            $m = 10;
                        }
                        $msg2 .= implode(',', $arr) . 'x' . $m . 'n.' . "\n";
                    }
                }
            }
        }

        return [$msg1, $msg2];
    }
}
