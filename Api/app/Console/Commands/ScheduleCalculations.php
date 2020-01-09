<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\Legend\CommonFunctions;
use Symfony\Component\DomCrawler\Crawler;

class ScheduleCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính lãi lỗ dựa vào ticket và kết quả sx lưu vào bảng profit_loss cho từng người 1';

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
        $url = "https://xskt.com.vn/rss-feed/mien-bac-xsmb.rss";
        $crawler = new Crawler(CommonFunctions::retrieveData($url, false));
        try {
            $result = "";
            $crawler->filterXPath('//channel/item')->each(function ($node, $index) use (&$result) {
                if ($index == 0) {
                    $result = $node->filter('description')->text();
                }
            });
            $result = str_replace(['-', 'ĐB:', '1:', '2:', '3:', '4:', '5:', '6:', '7:'], 'a', $result);
            $result = explode('a', $result);
            $data = [];
            if (!empty($result)) {
                foreach ($result as $item) {
                    if ($item !== "") {
                        $data[] = substr(trim($item), -2);
                    }
                }
            }
            $this->ticketHandle($data);
        } catch (\Exception $ex) {
            $this->info($ex->getMessage());
        }
    }

    public function ticketHandle($result)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        $tickets = Ticket::where('updated_at', '>', $currentDate . ' 00:00:00')
            ->where('updated_at', '<', $currentDate . ' 23:59:59')
            ->get();
        if (empty($tickets)) {
            $this->info('Không tìm thấy ticket nào !');
        }
        foreach ($tickets as $ticket) {
            if ($ticket['type'] == 1) {
                $this->breakStringNumber($ticket['chuoi_so']);
            }
        }
    }

    public function breakStringNumber($str)
    {
        $str = "tong1,cham1,99";
        $str = explode(',', $str);
        $result = [];
        foreach ($str as $item) {
            $item = strtolower($item);
            if (strpos($item, 'dau') !== false) {
                $result = array_merge($result, CommonFunctions::dauX($item));
            } elseif
            (strpos($item, 'dit') !== false) {
                $result = array_merge($result, CommonFunctions::ditX($item));
            } elseif (strpos($item, 'bo') !== false) {
                $result = array_merge($result, CommonFunctions::boXY($item));
            } elseif (strpos($item, 'tong') !== false) {
                $result = array_merge($result, CommonFunctions::tongX($item));
            } elseif (strpos($item, 'kepbang') !== false) {
                $result = array_merge($result, CommonFunctions::kepBang());
            } elseif (strpos($item, 'keplech') !== false) {
                $result = array_merge($result, CommonFunctions::kepLech());
            } elseif (strpos($item, 'cham') !== false) {
                $result = array_merge($result, CommonFunctions::chamX($item));
            } else {
                $result = array_merge($result, [$item]);
            }
        }
        $result = array_unique($result);
        return $result;
    }


}
