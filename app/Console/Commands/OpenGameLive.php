<?php

namespace App\Console\Commands;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OpenGameLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qry:open-game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cronjob run each minute to open Game when start.';

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
        return $this->openGame();
    }

    /**
     * @return bool
     */
    protected function openGame()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $first_init_game = Game::where('status', Game::STATUS_DEFAULT)
            ->where('date', '<=', $now)
            ->orderBy('date', 'desc')
            ->first();

        if ($first_init_game) {
            // Mark this game open live
            $first_init_game->status = Game::STATUS_OPENED;
            $first_init_game->save();

            // End game over time
            $this->endGames($now);

            return true;
        }

        return false;
    }

    /**
     * @param $time
     */
    protected function endGames($time)
    {
        $games = Game::where('status', Game::STATUS_DEFAULT)
            ->where('date', '<=', $time)
            ->get();

        if ($games->isNotEmpty()) {
            foreach ($games as $game) {
                $game->status = Game::STATUS_ENDED;
                $game->save();
            }
        }
    }
}
