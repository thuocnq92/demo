<?php

namespace App\Listeners;

use App\Events\GameResultShowed;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CalculateSevenDayAmountForUsers
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
     * @param  GameResultShowed $event
     * @return void
     */
    public function handle(GameResultShowed $event)
    {
        $date = Carbon::now();
        $sevenDaysPrevious = $date->copy()->subDays(7)->format('Y-m-d');
        $today = $date->copy()->format('Y-m-d');

        $query = Transaction::where('type', Transaction::TYPE_RECEIVE_MONEY)
            ->whereDate('date', '<=', $today)
            ->whereDate('date', '>=', $sevenDaysPrevious)
            ->orderBy('user_id');

        if (count($event->user_ids)) {
            $query->whereIn('user_id', $event->user_ids);
        }

        $transactions = $query->get();

        // Get list users have seven_days_amount to hide if have not transactions
        $hide_seven_days_users = User::isActived()
            ->where('seven_days_amount', '!=', 0)
            ->get();

        $arrays = [];
        if (count($transactions)) {
            foreach ($transactions as $transaction) {
                $arrays[$transaction->user_id][] = $transaction;
            }

            if (count($arrays)) {
                foreach ($arrays as $user_id => $txns) {
                    $total_seven_days_amount = 0;
                    foreach ($txns as $txn) {
                        $total_seven_days_amount += $txn->txn_amount;
                    }

                    $user = User::where('id', $user_id)->first();
                    if ($user) {
                        // Remove user have transaction from hide_seven_days_users
                        $hide_seven_days_users = $hide_seven_days_users->filter(function ($hide_user) use ($user) {
                            return $hide_user->id != $user->id;
                        });

                        $user->seven_days_amount = $total_seven_days_amount;
                        // If in seven days previous not have receive hide ranking of user
                        if ($total_seven_days_amount == 0) {
                            $user->show_ranking = User::HIDE_RANK;
                        } else {
                            $user->show_ranking = User::SHOW_RANK;
                        }
                        $user->save();

                        Log::info('Create seven days transaction for user_id: ' . $user->id . ' with seven_days_amount is: ' . $total_seven_days_amount);
                    }
                }
            }
        }

        if (count($event->user_ids) == 0) {
            // If not have a transactions about 7 days to set all users hide ranks
            foreach ($hide_seven_days_users as $user) {
                $user->seven_days_amount = 0;
                $user->show_ranking = User::HIDE_RANK;
                $user->save();
            }
        }
    }
}
