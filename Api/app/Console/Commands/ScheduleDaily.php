<?php

namespace App\Console\Commands;

use App\Models\Daily;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ScheduleDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hằng ngày vào lúc 18h40 sẽ tạo dữ liệu cho ngày mới';

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
            $tomorrow = Carbon::now()->addDay()->format('d-m-Y');
            $daily = Daily::where('date', $tomorrow)->first();
            if (!empty($daily)) {
                $this->info('Bản ghi đã được tạo!');
                return;
            }
            Daily::create(['date' => $tomorrow]);
        } catch (\Exception $ex) {
            $this->info($ex->getMessage());
        }
    }
}
