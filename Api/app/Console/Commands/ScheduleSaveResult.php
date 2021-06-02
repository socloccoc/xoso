<?php

namespace App\Console\Commands;

use App\Models\Result;
use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
use App\Models\Ticket;
use Carbon\Carbon;
use App\Helpers\Legend\CommonFunctions;
use Symfony\Component\DomCrawler\Crawler;
use drupol\phpermutations\Generators\Combinations;

class ScheduleSaveResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:result';

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
        $url = "https://www.xosominhngoc.com/ket-qua-xo-so/mien-bac.html";
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
        $baCang = 000;
        if (!empty($result)) {
            foreach ($result as $ind => $item) {
                if ($ind == 0) {
                    $baCang = substr(trim($item), -3);
                }
                if ($item !== "") {
                    $data[] = substr(trim($item), -2);
                }
            }
        }
        $de = $data[0];
        $lo = array_count_values($data);
        $input['de'] = [
            'number' => $de,
            'repetition' => 1,
        ];
        $input['bacang'] = [
          'number' => $baCang,
          'repetition' => 1,
        ];
        foreach ($lo as $index => $item){
            $input['lo'][] = [
                'number' => $index,
                'repetition' => $item,
            ];
        }
        $currentDate = Carbon::now()->format('d-m-Y');
        Result::insert(['date' => $currentDate, 'result' => json_encode($input)]);

    }
}
