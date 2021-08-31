<?php

namespace App\Console\Commands;

use App\Models\SummaryResult;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScheduleGetNumbersToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:get';

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
        // lấy những số mà 6 ngày chưa về
        $results = SummaryResult::orderBy('id', 'desc')->select('dac_biet', 'nhat', 'nhi_1', 'nhi_2', 'ba_1', 'ba_2', 'ba_3', 'ba_4', 'ba_5', 'ba_6',
            'tu_1', 'tu_2', 'tu_3', 'tu_4', 'nam_1', 'nam_2', 'nam_3', 'nam_4', 'nam_5', 'nam_6', 'sau_1', 'sau_2', 'sau_3', 'bay_1', 'bay_2', 'bay_3', 'bay_4')
            ->limit(6)->get()->toArray();
        $arr = [];
        foreach ($results as $result) {
            $a = array_values($result);
            $arr = array_unique(array_merge($arr, $a));
        }

        $arr2 = [];
        for ($i = 0; $i < 100; $i++) {
            $j = $i;
            if ($i < 10) {
                $j = '0' . $i;
            }
            $arr2[] = (string)$j;
        }

        $rl = [];
        foreach ($arr2 as $item) {
            if (!in_array($item, $arr)) {
                $rl[] = $item;
            }
        }

        // -1001466757473
        $t = '';
        foreach ($rl as $item){
            $t .= $item."\n";
        }

        Telegram::sendMessage([
            'chat_id'    => "-1001568475242",
            'parse_mode' => 'HTML',
            'text'       => "<b>Những số 6 ngày chưa về " . "</b>"."\n".$t,
        ]);
    }
}
