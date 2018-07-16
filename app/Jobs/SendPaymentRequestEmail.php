<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPaymentRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
    	  $bank = $this->user->bank;
        $data = [
            'user' => $this->user,
            'transaction' => $this->transaction,
            'bank' => $bank,
        ];

        $mailer->send('emails.payment_request', $data, function ($message) {
            $message->subject(trans('messages.email.payment_request.subject'));
            $message->from(env('MAIL_FROM', 'example@example.com'), env('MAIL_NAME', 'example@example.com'));
            $message->to($this->user->email);
        });
    }
}
