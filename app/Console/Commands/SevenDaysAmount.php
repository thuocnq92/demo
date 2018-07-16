<?php

namespace App\Console\Commands;

use App\Events\GameResultShowed;
use Illuminate\Console\Command;

class SevenDaysAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qry:seven-days-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create for cronjob to calculate total money receive about 7 days previous';

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
        // To calculate all users, array is empty
        $user_ids = [];
        event(new GameResultShowed($user_ids));
    }
}
