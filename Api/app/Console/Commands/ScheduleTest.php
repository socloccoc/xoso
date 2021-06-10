<?php

namespace App\Console\Commands;

use App\Helpers\Legend\CommonFunctions;
use App\Models\SummaryResult;
use Illuminate\Console\Command;

class ScheduleTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:start';

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
        $results = SummaryResult::all();
        $binary = [];
        for ($i = 0; $i < count($results) - 1; $i++) {
            $nhi_1 = CommonFunctions::convertToBinary($results[$i]['nhi_1']);
            $nhi_2 = CommonFunctions::convertToBinary($results[$i]['nhi_2']);
            $binary[$nhi_1.$nhi_2][] = CommonFunctions::convertToBinary($results[$i]['dac_biet']);
        }
        dd(array_count_values($binary['1100']));
    }
}
