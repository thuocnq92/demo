<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class TransactionController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('transactions.index');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function transactionData(Request $request)
    {
        $query = Transaction::with('user.phone');
        $datatables = Datatables::of($query);

        $datatables->addColumn('id', function ($txn) {
            return $txn->id;
        })->addColumn('phone', function ($txn) {
            return $txn->user->phone_number;
        })->addColumn('username', function ($txn) {
            return $txn->user->name;
        })->addColumn('date', function ($txn) {
            return $txn->date->format('Y/m/d H:i');
        })->addColumn('type', function ($txn) {
            return $txn->type_name;
        })->addColumn('txn_amount', function ($txn) {
            return $txn->txn_amount . ' ¥';
        })->addColumn('txn_fee', function ($txn) {
            return $txn->txn_fee;
        })->addColumn('note', function ($txn) {
            return $txn->note;
        });

        // Filter
        $datatables->filter(function ($query) use ($request) {
            if ($request->has('txn_phone')) {
                $query->whereHas('user.phone', function ($q) use ($request) {
                    $q->where('phone', $request->get('txn_phone'));
                });
            }

            if ($request->has('txn_date')) {
                $date = Carbon::createFromFormat('Y-m-d', $request->get('txn_date'));
                $query->whereDate('date', $date->format('Y-m-d'));
            }

            return $query;
        });

        $datatables->orderColumn('id', '-id $1')
            ->orderColumn('date', '-date $1')
            ->orderColumn('type', '-type $1')
            ->orderColumn('txn_amount', '-txn_amount $1');

        return $datatables->make(true);
    }

}
