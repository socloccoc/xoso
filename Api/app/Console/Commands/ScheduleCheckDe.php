<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
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
        $currentDate = Carbon::now()->subDay()->format('d-m-Y');
        $daily       = Daily::where('date', $currentDate)->first();
        if (empty($daily)) {
            return $this->sendError('Daily không tồn tại !', Response::HTTP_NOT_FOUND);
        }

        // danh sách customer_daily theo customer
        $listCustomerDaily = CustomerDaily::where('daily_id', $daily['id'])->pluck('id')->toArray();

        if (empty($listCustomerDaily)) {
            return $this->sendError('Customer_daily không tồn tại !', Response::HTTP_NOT_FOUND);
        }

        $des = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 1)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->get();
        $bacangs = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 4)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->get();
        $deMsg     = '<b>Đề : </b>';
        $bacangMsg = '<b>Ba Càng : </b>';
        foreach ($des as $point) {
            if ($point['sum'] >= 200000) {
                $deMsg .= $point['num'] . ': ' . $point['sum'] / 1000 . 'n <b>|</b> ';
            }
        }

        foreach ($bacangs as $point) {
            if ($point['sum'] >= 100000) {
                $bacangMsg .= $point['num'] . ': ' . $point['sum'] / 1000 . 'n <b>|</b> ';
            }
        }

        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
        . $deMsg . "\n"
        . $bacangMsg;

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);
    }
}
