<?php

namespace App\Listeners;

use App\Events\UserAnswered;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MarkUserPlayedAndIncreaseHeartReferred
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserAnswered $event
     * @return void
     */
    public function handle(UserAnswered $event)
    {
        $user = $event->user;
        // After user answer question mark user had play game.
        if ($user->is_played == 0) {
            $user->is_played = 1;

            // If User affiliate is_played to increase 1 heart for two persons
            if (!empty($user->referred_by)) {
                $user_refer = User::where('id', $user->referred_by)
                    ->where('is_played', 1)
                    ->first();
                if ($user_refer) {
                    $user_refer->fukkatu += 1;
                    $user->fukkatu += 1;

                    $user_refer->save();

                    // Write log
                    Log::info('Increase fukkatu for user id: ' . $user->id . ' with: ' . $user->fukkatu . ' And user ref is user_id: ' . $user_refer->id . ' with: ' . $user_refer->fukkatu);
                }
            }

            // If users referred_by user will get heart too when user played
            $users_refer_by = User::where('referred_by', $user->id)
                ->where('is_played', 1)
                ->get();
            if (count($users_refer_by)) {
                foreach ($users_refer_by as $item) {
                    $item->fukkatu += 1;
                    $user->fukkatu += 1;

                    $item->save();

                    // Write log
                    Log::info('Increase fukkatu for user id: ' . $user->id . ' with: ' . $user->fukkatu . ' And user ref is user_id: ' . $item->id . ' with: ' . $item->fukkatu);
                }
            }

            $user->save();
        }
    }
}
