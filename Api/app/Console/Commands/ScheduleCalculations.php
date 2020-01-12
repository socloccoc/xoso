<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDaily;
use App\Models\Daily;
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
            $this->updateResultDaily($result);
            $result = str_replace(['-', 'ĐB:', '1:', '2:', '3:', '4:', '5:', '6:', '7:'], 'a', $result);
            $result = explode('a', $result);
            $data = [];
            $baCang = 000;
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
        $currentDate = Carbon::now()->format('d-m-Y');
        $daily = Daily::where('date', $currentDate)->first();
        if (empty($daily)) {
            $this->info('Daily không tồn tại !');
            return;
        }
        $cutomerDailyIds = CustomerDaily::where('daily_id', $daily['id'])->pluck('id')->toArray();
        $tickets = Ticket::whereIn('customer_daily_id', $cutomerDailyIds)->get();
        if (empty($tickets)) {
            $this->info('Không tìm thấy ticket nào !');
        }
        foreach ($tickets as $ticket) {
            try {
                $customer = $this->getCustomerByTicket($ticket['id']);
                $newTicket['win'] = "";
                $newTicket['win_num'] = 0;
                $newTicket['profit'] = 0;
                $newTicket['status'] = 1;

                // đề
                if ($ticket['type'] == 1) {
                    $arr = $this->breakStringNumber($ticket['chuoi_so']);
                    $prize = $this->checkDe($result, $arr);
                    foreach ($prize as $ind => $item) {
                        $profit = $item * $ticket['diem_tien'] * $customer['de_percent'];
                        $newTicket['win'] = $ind . ':' . $item;
                        $newTicket['win_num'] = $item;
                        $newTicket['profit'] = $profit;
                    }
                    $this->updateTiket($ticket['id'], $newTicket);
                    continue;
                }

                // lô
                if ($ticket['type'] == 0) {
                    $arr = $this->breakStringNumber($ticket['chuoi_so']);
                    $prize = $this->checkLo($result, $arr);
                    $profit = 0;
                    foreach ($prize as $ind => $item) {
                        $profit += $item * $ticket['diem_tien'] * $customer['lo_percent'];
                        if ($newTicket['win'] == "") {
                            $newTicket['win'] .= $ind . ':' . $item;
                        } else {
                            $newTicket['win'] .= "," . $ind . ':' . $item;
                        }
                        $newTicket['win_num'] += $item;
                    }
                    $newTicket['profit'] = $profit;
                    $this->updateTiket($ticket['id'], $newTicket);
                    continue;
                }

                // xiên
                if ($ticket['type'] == 2) {
                    $arr = explode(',', $ticket['chuoi_so']);
                    $profit = 0;
                    foreach ($arr as $item) {
                        $itemE = explode('-', $item);
                        $prize = $this->checkLo($result, $itemE);
                        if (count($itemE) == count($prize)) {
                            $profit += min($prize) * $ticket['diem_tien'] * self::PERCENT[count($itemE)];
                            if ($newTicket['win'] == "") {
                                $newTicket['win'] .= $item . ':' . min($prize);
                            } else {
                                $newTicket['win'] .= "," . $item . ':' . min($prize);
                            }
                            $newTicket['win_num'] += min($prize);
                        }
                    }
                    $newTicket['profit'] = $profit;
                    $this->updateTiket($ticket['id'], $newTicket);
                    continue;
                }

                // xiên quay
                if ($ticket['type'] == 3) {
                    $arr = $this->combinations($ticket['chuoi_so']);
                    $profit = 0;

                    foreach ($arr as $item) {
                        $prize = $this->checkLo($result, $item);
                        if (count($item) == count($prize)) {
                            $profit += min($prize) * $ticket['diem_tien'] * self::PERCENT[count($item)];
                            if ($newTicket['win'] == "") {
                                $newTicket['win'] .= implode('-', $item) . ':' . min($prize);
                            } else {
                                $newTicket['win'] .= "," . implode('-', $item) . ':' . min($prize);
                            }
                            $newTicket['win_num'] += min($prize);
                        }
                    }
                    $newTicket['profit'] = $profit;
                    $this->updateTiket($ticket['id'], $newTicket);
                    continue;
                }

                // ba càng
                if ($ticket['type'] == 4) {
                    $arr = explode(',', $ticket['chuoi_so']);
                    foreach ($arr as $item) {
                        if ($item == $baCang) {
                            $profit = $ticket['diem_tien'] * 400;
                            $newTicket['win'] = $item . ':1';
                            $newTicket['win_num'] = 1;
                            $newTicket['profit'] = $profit;
                            break;
                        }
                    }
                    $this->updateTiket($ticket['id'], $newTicket);
                }
            } catch (\Exception $ex) {
                $this->info($ex->getMessage());
                continue;
            }
        }
        $this->syntheticTicket();
    }

    public function updateResultDaily($result){
        $currentDate = Carbon::now()->format('d-m-Y');
        $daily = Daily::where('date', $currentDate)->first();
        if (empty($daily)) {
            $this->info('Không tìm thấy daily !');
            return;
        }
        $daily->update(['result' => $result]);
    }

    public function syntheticTicket()
    {
        $currentDate = Carbon::now()->format('d-m-Y');
        $daily = Daily::where('date', $currentDate)->first();
        if (empty($daily)) {
            $this->info('Không tìm thấy daily !');
            return;
        }
        $customerDaily = CustomerDaily::where('daily_id', $daily['id'])->get();
        if (empty($customerDaily)) {
            $this->info('Không tìm thấy customerDaily !');
            return;
        }

        foreach ($customerDaily as $item) {
            $data['money_in'] = 0;
            $data['money_out'] = 0;
            $data['profit'] = 0;
            $data['match'] = "";
            $tickets = Ticket::where('customer_daily_id', $item['id'])->get();
            if (empty($tickets)) {
                continue;
            }
            foreach ($tickets as $ticket) {
                $data['money_in'] += $ticket['fee'];
                $data['profit'] += $ticket['profit'];
                if ($ticket['win'] !== "") {
                    if ($data['match'] == "") {
                        $data['match'] .= $ticket['win'];
                    } else {
                        $data['match'] .= "," . $ticket['win'];
                    }
                }
            }
            $data['money_out'] = $data['profit'] - $data['money_in'];
            CustomerDaily::where('id', $item['id'])->limit(1)->update($data);
        }
    }

    public function updateTiket($id, $ticket)
    {
        $ticket = Ticket::where('id', $id)->limit(1)->update($ticket);
        if (!$ticket) {
            $this->info('Cập nhật ticket thất bại');
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
