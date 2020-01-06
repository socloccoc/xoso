<?php

namespace App\Console\Commands;

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
                if($index == 0){
                    $data = $node->filter('description')->text();
                }
            });
            dd($result);
        } catch (\Exception $ex) {
            $this->info($ex->getMessage());
        }
    }


}
