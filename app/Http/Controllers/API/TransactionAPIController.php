<?php

namespace App\Http\Controllers\API;

use App\Jobs\SendPaymentRequestEmail;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionAPIController extends APIBaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = 20;
        if ($request->has('limit') && !empty($request->get('limit'))) {
            $limit = intval(str_replace(" ", "", $request->get('limit')));
        }
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate($limit);

        $result = [
            'transactions' => $transactions
        ];

        return $this->sendResponse($result, 'Get list transactions success.');
    }

    /**
     * Generate transaction by payment request from user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentRequest(Request $request)
    {
        $user = Auth::user();
        $min = (int)env('PAYMENT_REQUEST_MIN', 500);
        $fee = (int)env('PAYMENT_REQUEST_FEE', 200);

        $rule = [
            'amount' => 'required|integer|min:' . $min . '|max:' . $user->current_amount,
            'email' => 'required|email',
        ];

        $attribute = [
            'amount' => 'Amount',
            'email' => 'Email',
        ];

        $validator = Validator::make($request->all(), $rule, [], $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        if (!$user->email) {
            $user->email = $request->get('email');
            $user->save();
        }

        $amount = (int)$request->get('amount');

        // Decrease amount direct
        $user->current_amount = $user->current_amount - $amount;
        $user->save();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->date = Carbon::now();
        $transaction->type = Transaction::TYPE_WITHDRAW_BANK;
        $transaction->txn_amount = -$amount;
        $transaction->current_amount = $user->current_amount;
        $transaction->txn_fee = $fee;
        $transaction->note = '銀行振込';

        $transaction->save();

        $this->dispatch(new SendPaymentRequestEmail($user, $transaction));

        $result = [
            'transaction' => $transaction
        ];

        return $this->sendResponse($result, 'Send payment request success');
    }
}
