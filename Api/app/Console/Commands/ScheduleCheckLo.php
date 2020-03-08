<?php

namespace App\Console\Commands;

use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Ticket;
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
            return;
        }
        $cutomerDailyIds = CustomerDaily::where('daily_id', $daily['id'])->pluck('id')->toArray();
        $tickets         = Ticket::whereIn('customer_daily_id', $cutomerDailyIds)->get();
        if (empty($tickets)) {
            $this->info('Không tìm thấy ticket nào !');
        }
        $loMsg   = '<b>Lô : </b>';
        $xienMsg = '<b>Xiên : </b>';
        foreach ($tickets as $ticket) {
            if ($ticket['type'] == 0 && $ticket['diem_tien'] >= 250) {
                $loMsg .= $ticket['chuoi_so'] . ': ' . $ticket['diem_tien'] . 'đ <b>|</b> ';
            }
            if (($ticket['type'] == 2 || $ticket['type'] == 3) && $ticket['diem_tien'] >= 300000) {
                $xienMsg .= $ticket['chuoi_so'] . ': ' . $ticket['diem_tien'] / 1000 . 'n <b>|</b> ';
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
