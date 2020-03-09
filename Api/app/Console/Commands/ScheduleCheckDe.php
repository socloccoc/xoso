<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckDe extends Command {
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
        $deMsg     = "<b>De. </b> \n";
        $bacangMsg = "<b>Bacang. </b> \n";
        foreach ($des as $point) {
            if ($point['sum'] >= 200000) {
                $deMsg .= $point['num'] . 'x' . $point['sum'] / 1000 . 'n.' . "\n";
            }
        }

        foreach ($bacangs as $point) {
            if ($point['sum'] >= 100000) {
                $bacangMsg .= $point['num'] . 'x' . $point['sum'] / 1000 . 'n.' . "\n";
            }
        }

        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
        . $deMsg
        . $bacangMsg;

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);
    }
}
