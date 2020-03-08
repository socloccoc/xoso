<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
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

        $los = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->where('type', 0)
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->get();
        $xiens = Point::whereIn('customer_daily_id', $listCustomerDaily)
            ->whereIn('type', [2, 3])
            ->selectRaw('points.*, sum(diem_tien) as sum')
            ->groupBy('points.num')
            ->get();
        $loMsg   = '<b>Lô : </b>';
        $xienMsg = '<b>Xiên : </b>';
        foreach ($los as $point) {
            if ($point['sum'] >= 250) {
                $loMsg .= $point['num'] . ': ' . number_format($point['sum']) . 'đ <b>|</b> ';
            }
        }

        foreach ($xiens as $point) {
            if ($point['sum'] >= 300000) {
                $xienMsg .= $point['num'] . ': ' . number_format($point['sum'] / 1000) . 'n <b>|</b> ';
            }
        }

        $text = "<b>Thông tin bộ số lớn ngày " . $currentDate . "</b>\n"
        . $loMsg . "\n"
        . $xienMsg;

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);
    }
}
