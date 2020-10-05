<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Point;
use App\Models\Ticket;
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
        try {
            $currentDate = Carbon::now()->format('d-m-Y');
            $users       = User::all();
            $daily       = Daily::where('date', $currentDate)->first();
            if (empty($daily)) {
                $this->info('Daily không tồn tại !');
                return false;
            }

            $userResult = '';

            $userNameMaxLength = 0;
            foreach ($users as $user) {
                if($userNameMaxLength < mb_strlen($user['name'])){
                    $userNameMaxLength = mb_strlen($user['name']);
                }
            }

            foreach ($users as $user) {
                $profit   = 0;
                $userName = $this->getUserName($user['name'], $userNameMaxLength);
                // lấy ra danh sách customer theo user
                $listCustomerByUser = [];
                if ($user['type'] == 1) {
                    $listCustomerByUser = Customer::where('user_id', $user['id'])->pluck('id')->toArray();
                    if (empty($listCustomerByUser)) {
                        $userResult .= $userName . ' : ' . number_format($profit) . "\n";
                        continue;
                    }
                }
                $listCustomerDaily = CustomerDaily::where(function ($q) use ($user, $listCustomerByUser) {
                    if ($user['type'] == 1) {
                        $q->whereIn('customer_id', $listCustomerByUser);
                    }
                })->where('daily_id', $daily['id'])->pluck('id')->toArray();

                $points = Point::whereIn('customer_daily_id', $listCustomerDaily)
                    ->groupBy('type')
                    ->selectRaw('type, sum(diem_tien) as diem_tien')
                    ->get()->toArray();

                $tickets = Ticket::whereIn('customer_daily_id', $listCustomerDaily)->groupBy('type')
                    ->selectRaw('type, sum(sales) as doanh_so, sum(profit) as tien_trung')
                    ->get()->toArray();

                if (empty($points) || empty($tickets)) {
                    $userResult .= $userName . ' : ' . number_format($profit) . "\n";
                    continue;
                }

                foreach ($points as $point) {
                    foreach ($tickets as $ticket) {
                        $rate = 0;
                        if ($point['type'] == 0) {
                            $rate = $user['lo_rate'];
                        } elseif ($point['type'] == 1) {
                            $rate = $user['de_rate'];
                        } elseif ($point['type'] == 2 || $point['type'] == 3) {
                            $rate = $user['xien_rate'];
                        } else {
                            $rate = $user['bacang_rate'];
                        }
                        if ($point['type'] == $ticket['type']) {
                            $profit += $point['diem_tien'] * $rate - $ticket['tien_trung'];
                        }
                    }
                }
                $userResult .= $userName . ' : ' . number_format($profit) . "\n";
            }
            $text = "<b>Thông tin lợi nhuận ngày " . $currentDate . "</b>\n"
            . $userResult;
            Telegram::sendMessage([
                'chat_id'    => config('constants.CHANNEL_ID'),
                'parse_mode' => 'HTML',
                'text'       => $text,
            ]);
        } catch (\Exception $ex) {
            $this->info($ex->getMessage());
        }
    }

    public function getUserName($name, $maxLength)
    {
        $n = $maxLength - mb_strlen($name);
        if ($name == "Tâm") {
            $n = $n + 3;
        } elseif ($name == "Sơn") {
            $n = $n + 4;
        } elseif ($name == "admin" || $name == "Tuyền") {
            $n = $n + 2;
        }
        for ($i = 0; $i < $n; $i++) {
            $name .= ' ';
        }
        return $name;
    }

//    public function handle() {
    //        $currentDate = Carbon::now()->format('d-m-Y');
    //        $daily       = Daily::where('date', $currentDate)->first();
    //        if (empty($daily)) {
    //            $this->info('Daily không tồn tại !');
    //            return;
    //        }
    //        $users = User::where('type', 1)->get();
    //
    //        $userResult = '';
    //        $all        = 0;
    //        foreach ($users as $user) {
    //            $customersByUser = Customer::where('user_id', $user['id'])->get();
    //            if (empty($customersByUser)) {
    //                continue;
    //            }
    //            $userResult .= $user['name'] . ' : ';
    //            $totalMoneyoutOfUser = 0;
    //            foreach ($customersByUser as $customer) {
    //                $totalMoneyoutOfCustomer = CustomerDaily::where('daily_id', $daily['id'])->where('customer_id', $customer['id'])->sum('money_out');
    //                $totalMoneyoutOfUser += $totalMoneyoutOfCustomer;
    //                $all += $totalMoneyoutOfCustomer;
    //            }
    //            $userResult .= number_format(-$totalMoneyoutOfUser) . "\n";
    //        }
    //
    //        $text = "<b>Thông tin lợi nhuận ngày " . $currentDate . "</b>\n"
    //        . 'Tổng: ' . number_format(-$all) . "\n"
    //        . $userResult;
    //
    //        Telegram::sendMessage([
    //            'chat_id'    => config('constants.CHANNEL_ID'),
    //            'parse_mode' => 'HTML',
    //            'text'       => $text,
    //        ]);
    //    }
}
