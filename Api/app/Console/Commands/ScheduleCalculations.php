<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\Legend\CommonFunctions;
use Symfony\Component\DomCrawler\Crawler;
use drupol\phpermutations\Generators\Combinations;

class ScheduleCalculations extends Command
{
    const PERCENT = [2 => 10, 3 => 40, 4 => 100];
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
                foreach ($result as $ind => $item) {
                    if ($ind == 1) {
                        $baCang = substr(trim($item), -3);
                    }
                    if ($item !== "") {
                        $data[] = substr(trim($item), -2);
                    }
                }
            }
            $this->ticketHandle($data, $baCang);
        } catch (\Exception $ex) {
            $this->info($ex->getMessage());
        }
    }

    public function ticketHandle($result, $baCang)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        $tickets = Ticket::where('updated_at', '>', $currentDate . ' 00:00:00')
            ->where('updated_at', '<', $currentDate . ' 23:59:59')
            ->get();
        if (empty($tickets)) {
            $this->info('Không tìm thấy ticket nào !');
        }
        foreach ($tickets as $ticket) {
            $customer = $this->getCustomerByTicket($ticket['id']);
            if ($ticket['type'] == 1) {
                $arr = $this->breakStringNumber($ticket['chuoi_so']);
                $prize = $this->checkDe($result, $arr);
                foreach ($prize as $ind => $item) {
                    $profit = $item * $ticket['diem_tien'] * $customer['de_percent'];
                    dd($profit);
                }
                // update ticket
            }
            if ($ticket['type'] == 0) {
                $arr = $this->breakStringNumber($ticket['chuoi_so']);
                $prize = $this->checkLo($result, $arr);
                $profit = 0;
                foreach ($prize as $ind => $item) {
                    $profit += $item * $ticket['diem_tien'] * $customer['lo_percent'];
                }
                dd($profit);
                // update ticket
            }

            if ($ticket['type'] == 2) {
                $arr = explode(',', $ticket['chuoi_so']);
                $profit = 0;
                foreach ($arr as $item) {
                    $item = explode('-', $item);
                    $prize = $this->checkLo($result, $item);
                    if (count($item) == count($prize)) {
                        $profit += $ticket['diem_tien'] * self::PERCENT[count($item)];
                    }
                }
                dd($profit);
                // update ticket
            }

            if ($ticket['type'] == 3) {
                $arr = $this->combinations($ticket['chuoi_so']);
                $profit = 0;
                foreach ($arr as $item) {
                    $prize = $this->checkLo($result, $item);
                    if (count($item) == count($prize)) {
                        $profit += $ticket['diem_tien'] * self::PERCENT[count($item)];
                    }
                }
                dd($profit);
                // update ticket
            }

            if ($ticket['type'] == 4) {
                $arr = explode(',', $ticket['chuoi_so']);
                foreach ($arr as $item) {
                    if ($item == $baCang) {
                        $profit = $ticket['diem_tien'] * 400;
                        dd($profit);
                        // update ticket
                        break;
                    }
                }

            }
        }
    }

    public function combinations($str)
    {
        $result = [];
        $arrs = explode(',', $str);
        foreach ($arrs as $arr) {
            $ep = explode('-', $arr);
            if (count($ep) >= 3) {
                $com = new Combinations($ep, count($ep) - 1);
                $result = array_merge($result, $com->toArray());
            }
            $result[] = $ep;
        }
        return $result;
    }

    public function breakStringNumber($str)
    {
//        $str = "tong1,cham1,99";
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
//        $result = array_unique($result);
//        dd($result);
        return $result;
    }

    public function checkDe($result, $arr)
    {
        $data = [];
        for ($i = 0; $i < count($arr); $i++) {
            if ($result[0] == $arr[$i]) {
                if (isset($data[$arr[$i]])) {
                    $data[$arr[$i]] = $data[$arr[$i]] + 1;
                } else {
                    $data[$arr[$i]] = 1;
                }
            }
        }
        return $data;
    }

    public function checkLo($result, $arr)
    {
        $data = [];
        for ($i = 0; $i < count($result); $i++) {
            for ($j = 0; $j < count($arr); $j++) {
                if ($result[$i] == $arr[$j]) {
                    if (isset($data[$arr[$j]])) {
                        $data[$arr[$j]] = $data[$arr[$j]] + 1;
                    } else {
                        $data[$arr[$j]] = 1;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * get customer by ticket
     * @param $ticketId
     * @return mixed
     */
    public function getCustomerByTicket($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->first();
        $customerDaily = CustomerDaily::where('id', $ticket['customer_daily_id'])->first();
        $customer = Customer::where('id', $customerDaily['customer_id'])->first();
        return $customer;
    }

}
