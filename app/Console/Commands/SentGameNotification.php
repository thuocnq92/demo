<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SentGameNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qry:sent-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cronjob run each minute to sent Game notifications.';

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
        $now = Carbon::now();
        // Get notifications of game
        $notification = Notification::with('game')
            ->where('is_sent', Notification::IS_NOT_SENT)
            ->where('time', '<=', $now->format('Y-m-d H:i:s'))
            ->orderBy('time', 'ASC')
            ->first();

        if ($notification) {
            $job = new \App\Jobs\SendPushNotificationJob($notification);

            dispatch($job);

            // If all notification is sent change is_notified is true
            Notification::checkGameNotificationSented($notification->game->id);
        }

        return true;
    }
}
