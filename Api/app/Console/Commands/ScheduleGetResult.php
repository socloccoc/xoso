<?php

namespace App\Console\Commands;

use App\Helpers\Legend\CommonFunctions;
use App\Models\Result;
use App\Models\SummaryResult;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class ScheduleGetResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'result:get';

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
        $lastResult = SummaryResult::orderBy('date', 'desc')->first();
        $currentDate = Carbon::now()->format('Y-m-d');
        $startDate = isset($lastResult['date']) ? $lastResult['date'] : '2018-06-10';
        while ($startDate <= $currentDate) {
            $date = Carbon::createFromFormat('Y-m-d', $startDate)->format('d-m-Y');
            $url = "https://www.xosominhngoc.com/ket-qua-xo-so/mien-bac/" . $date . '.html';
            $crawler = new Crawler(CommonFunctions::retrieveData($url, false));
            $result = [];
            $crawler->filterXPath('//table[@class="bkqmiennam bkqmienbac"]/tbody')->each(function ($node, $index) use (&$result) {
                if ($index == 0) {
                    $matches2 = [];
                    $node->filter('tr')->each(function ($item, $index) use (&$matches2) {
                        if ($index > 2 && $index <= 10) {
                            preg_match_all('!\d+!', $item->text(), $matches);
                            $matches2[] = $matches[0][0];
                        }
                    });
                    foreach ($matches2 as $ind => $match) {
                        if ($ind == 2 || $ind == 3) {
                            $splitLength = 5;
                        }
                        if ($ind == 4 || $ind == 5) {
                            $splitLength = 4;
                        }
                        if ($ind == 6) {
                            $splitLength = 3;
                        }
                        if ($ind == 7) {
                            $splitLength = 2;
                        }
                        if ($ind == 0 || $ind == 1) {
                            $result[] = $match;
                        } else {
                            $parts = str_split($match, $splitLength);
                            foreach ($parts as $part) {
                                $result[] = $part;
                            }
                        }
                    }
                }
            });

            $data = [];
            if (!empty($result)) {
                foreach ($result as $ind => $item) {
                    if ($item !== "") {
                        $data[] = substr(trim($item), -2);
                    }
                }
            }

            $input = [
                'date'     => $startDate,
                'dac_biet' => $data[0],
                'nhat'     => $data[1],
                'nhi_1'    => $data[2],
                'nhi_2'    => $data[3],
                'ba_1'     => $data[4],
                'ba_2'     => $data[5],
                'ba_3'     => $data[6],
                'ba_4'     => $data[7],
                'ba_5'     => $data[8],
                'ba_6'     => $data[9],
                'tu_1'     => $data[10],
                'tu_2'     => $data[11],
                'tu_3'     => $data[12],
                'tu_4'     => $data[13],
                'nam_1'    => $data[14],
                'nam_2'    => $data[15],
                'nam_3'    => $data[16],
                'nam_4'    => $data[17],
                'nam_5'    => $data[18],
                'nam_6'    => $data[19],
                'sau_1'    => $data[20],
                'sau_2'    => $data[21],
                'sau_3'    => $data[22],
                'bay_1'    => $data[23],
                'bay_2'    => $data[24],
                'bay_3'    => $data[25],
                'bay_4'    => $data[26],
            ];

            if (!$this->checkExit($startDate)) {
                SummaryResult::create($input);
            }

            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->addDay()->format('Y-m-d');
            sleep(1);
        }

    }

    public function checkExit($date)
    {
        $record = SummaryResult::where('date', $date)->first();
        if ($record)
            return true;
        return false;
    }
}
