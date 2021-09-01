<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Legend\CommonFunctions;
use App\Models\SummaryResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Validator;

class ScheduleDeAnalytic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'de:analytic';

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
        try {
            $resultYesterday = SummaryResult::orderBy('id', 'DESC')->first()->toArray();
            $nhi_1 = CommonFunctions::convertToBinary($resultYesterday['nhi_1']);
            $nhi_2 = CommonFunctions::convertToBinary($resultYesterday['nhi_2']);

            if (!in_array($nhi_1, ['01', '10', '00', '11']) || !in_array($nhi_2, ['01', '10', '00', '11'])) {
                return response()->json(['success' => false, 'msg' => __("There was errors, please try again !")]);
            }

            $results = SummaryResult::all();
            $binary = [];
            for ($i = 0; $i < count($results) - 1; $i++) {
                if ($nhi_1 == CommonFunctions::convertToBinary($results[$i]['nhi_1']) && $nhi_2 == CommonFunctions::convertToBinary($results[$i]['nhi_2'])) {
                    $binary[$nhi_1 . $nhi_2][] = CommonFunctions::convertToBinary($results[$i]['dac_biet']);
                }
            }

            $pair = $nhi_1 . $nhi_2;

            $kq = array_count_values($binary[$pair]);
            $sum = array_sum($kq);
            $data = [
                '01' => $this->calculatePercent($kq['01'], $sum),
                '10' => $this->calculatePercent($kq['10'], $sum),
                '00' => $this->calculatePercent($kq['00'], $sum),
                '11' => $this->calculatePercent($kq['11'], $sum),
            ];

            $text = '';

            foreach ($data as $index => $item){
                $text .= $index . " -> " . $item . "\n";
            }

            Telegram::sendMessage([
                'chat_id'    => '-1001568475242',
                'parse_mode' => 'HTML',
                'text'       => "<b>Thông đề bộ số xác suất thấp nhất. </b>",
            ]);

            Telegram::sendMessage([
                'chat_id'    => '-1001568475242',
                'parse_mode' => 'HTML',
                'text'       => $text,
            ]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'msg' => __('There was errors, please try again !')]);
        }
    }

    public function calculatePercent($num, $sum)
    {
        return number_format($num / $sum * 100, 1);
    }
}
