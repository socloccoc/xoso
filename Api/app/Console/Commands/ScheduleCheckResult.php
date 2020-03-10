<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleCheckResult extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkResult:start';

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
        $users = User::where('type', 1)->get();

        $userResult = '';
        $all        = 0;
        foreach ($users as $user) {
            $customersByUser = Customer::where('user_id', $user['id'])->get();
            if (empty($customersByUser)) {
                continue;
            }
            $userResult .= $user['name'] . ' : ';
            $totalMoneyoutOfUser = 0;
            foreach ($customersByUser as $customer) {
                $totalMoneyoutOfCustomer = CustomerDaily::where('daily_id', $daily['id'])->where('customer_id', $customer['id'])->sum('money_out');
                $totalMoneyoutOfUser += $totalMoneyoutOfCustomer;
                $all += $totalMoneyoutOfCustomer;
            }
            $userResult .= number_format(-$totalMoneyoutOfUser) . "\n";
        }

        $text = "<b>Thông tin lợi nhuận ngày " . $currentDate . "</b>\n"
        . 'Tổng: ' . number_format(-$all) . "\n"
        . $userResult;

        Telegram::sendMessage([
            'chat_id'    => config('constants.CHANNEL_ID'),
            'parse_mode' => 'HTML',
            'text'       => $text,
        ]);
    }
}
