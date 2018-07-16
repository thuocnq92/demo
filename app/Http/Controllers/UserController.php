<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Facades\Datatables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query_game = Game::with(['questions', 'users', 'answers']);

        $data = [];
        $max_column_questions = 12;
        $user_find = null;

        if ($request->has('user_name') && !empty($request->get('user_name'))) {
            $data['user_name'] = $request->get('user_name');
        }

        if ($request->has('user_phone') && (!empty($request->get('user_phone')) || $request->get('user_phone') == 0)) {
            $data['user_phone'] = $request->get('user_phone');
        }

        if ($request->has('game_no') && !empty($request->get('game_no'))) {
            $query_game->where('id', $request->get('game_no'));
            $data['game_no'] = $request->get('game_no');
        }

        if ($request->has('game_date') && !empty($request->get('game_date'))) {
            $game_date = Carbon::createFromFormat('Y-m-d', $request->get('game_date'));
            $query_game->whereDate('date', $game_date->format('Y-m-d'));
            $data['game_date'] = $request->get('game_date');
        }

        if ($request->has('user_id') && !empty($request->get('user_id'))) {
            $data['user_id'] = $request->get('user_id');
            $user_find = User::with('phone')
                ->where('id', $request->get('user_id'))
                ->isActived()
                ->first();
            if (empty($user_find)) {
                $request->session()->flash('danger', 'User not found !');

                return redirect()->back();
            }

            $query_game->whereHas('users', function ($q) use ($request) {
                $q->where('user_id', $request->get('user_id'));
            });
        }

        $games = [];
        $game_users = [];

        if (!empty($request->get('game_no')) || !empty($request->get('game_date')) || !empty($request->get('user_id'))) {
            $games = $query_game->orderBy('id', 'DESC')
                ->get();
            if (count($games) == 0) {
                $request->session()->flash('warning', 'Game not found !');
            } else {
                $request->session()->flash('success', 'Find Game success !');
            }
        }

        // Get all users join a game
        if (count($games) && $user_find) {
            if ($user_find) {
                foreach ($games as $game) {
                    $answers = [];
                    $transaction_games = Transaction::where('type', Transaction::TYPE_RECEIVE_MONEY)
                        ->where('reference_game_id', $game->id)
                        ->get();
                    // User win game when have a transaction of game
                    $game_price = 0;
                    $is_win = false;
                    foreach ($transaction_games as $transaction_game) {
                        if ($transaction_game->user_id == $user_find->id) {
                            $is_win = true;
                            $game_price = $transaction_game->txn_amount;
                        }
                    }

                    foreach ($game->questions as $index => $question) {
                        $value_answer = [
                            'answer' => null,
                            'class_answer' => null
                        ];

                        foreach ($game->answers as $answer) {
                            if ($answer->user_id == $user_find->id && $answer->question_id == $question->id) {
                                $value_answer = [
                                    'answer' => $answer->answer,
                                    'class_answer' => $answer->result == Answer::WRONG_ANSWER
                                        ? 'cell-red'
                                        : (
                                        $answer->result == Answer::POINT_HEART
                                            ? 'cell-yellow'
                                            : null
                                        )
                                ];
                            }
                        }

                        $answers[$index] = $value_answer;
                    }
                    if (count($answers) < $max_column_questions) {
                        $black_cells = $max_column_questions - count($answers);
                        for ($x = 1; $x <= $black_cells; $x++) {
                            $answers[] = [
                                'answer' => null,
                                'class_answer' => 'cell-black'
                            ];
                        }
                    }

                    $game_users[] = [
                        'user_id' => $game->id,
                        'user_name' => $user_find->name,
                        'user_phone' => $user_find->phone->phone,
                        'game_date' => $game->date->format('Y/m/d'),
                        'is_win' => $is_win,
                        'answers' => $answers,
                        'game_price' => $game_price
                    ];
                }
            }
        }

        // Custom paginate
        // Get current page form url e.x. &page=1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Create a new Laravel collection from the array data
        $itemCollection = collect($game_users);

        // Define how many items we want to be visible in each page
        $perPage = 10;

        // Slice the collection to get the items to display in current page
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create our paginator and pass it to the view
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);

        // set url path for generted links
        $paginatedItems->setPath($request->url());

        $data['max_column_questions'] = $max_column_questions;
        $data['game_users'] = $currentPageItems;
        $data['pagination'] = $paginatedItems;

        return view('users.list', $data);
    }

    public function live_points($id, Request $request)
    {
        $request->session()->flash('success', 'Find Game success !');
        $user = User::find($id);

        if (!$user) {
            return [
                'code' => 404,
                'msg' => 'User not found',
            ];
        }

        $rule = [
            'live_points' => 'required|integer|min:1'
        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return [
                'code' => 422,
                'msg' => $validator->messages()->first(),
            ];
        }

        $user->fukkatu += (int)$request->get('live_points');
        if ($user->save()) {

            return [
                'code' => 200,
                'msg' => 'Add live points success',
            ];
        }

        return [
            'code' => 500,
            'msg' => 'Error internal',
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function usersData(Request $request)
    {
        $query = User::with(['phone', 'bank', 'answers' => function ($q) {
            $q->groupBy('user_id', 'game_id');
        }])
            ->isActived();

        $datatables = Datatables::of($query);

        $datatables->addColumn('id', function ($user) {
            return $user->id;
        })->addColumn('phone', function ($user) {
            return $user->phone->phone;
        })->addColumn('user_name', function ($user) {
            return $user->name;
        })->addColumn('affiliate_id', function ($user) {
            return $user->affiliate_id;
        })->addColumn('bank_name', function ($user) {
            return empty($user->bank) ? '' : $user->bank->bank_name;
        })->addColumn('bank_branch', function ($user) {
            return empty($user->bank) ? '' : $user->bank->bank_branch;
        })->addColumn('bank_id', function ($user) {
            return empty($user->bank) ? '' : $user->bank->bank_id;
        })->addColumn('total_amount', function ($user) {
            return '¥' . $user->total_amount;
        })->addColumn('current_amount', function ($user) {
            return '¥' . $user->current_amount;
        })->addColumn('fukkatu', function ($user) {
            return $user->fukkatu;
        })->addColumn('no_game_played', function ($user) {
            return empty($user->answers) ? 0 : $user->answers->count();
        })->addColumn('actions', function ($user) use ($request) {
            $button_delete = '<button type="button" 
            class="btn btn-flat btn-warning add-point-use"
            data-toggle="modal"
            data-target="#modal-add-live-point" 
            data-user_id="' . $user->id . '">
            <i class="fa fa-plus"></i> 
            Add life point
            </button>';

            $button_view = '<a href="' . route('users.index', ['user_name' => $request->get('user_name'), 'user_phone' => $request->get('user_phone'), 'user_id' => $user->id]) . '" class="btn btn-flat btn-info">
             <i class="fa fa-eye"></i> 
             View
             </a>';

            return $button_view . ' ' . $button_delete;
        })->rawColumns(['actions']);

        // Filter
        $datatables->filter(function ($query) use ($request) {
            if ($request->has('user_name')) {
                $query->where('name', 'like', '%' . $request->get('user_name') . '%');
            }

            if ($request->has('user_phone')) {
                $query->whereHas('phone', function ($q) use ($request) {
                    $q->where('phone', 'like', '%' . $request->get('user_phone') . '%');
                });
            }

            return $query;
        });

        $datatables->orderColumn('id', '-id $1');

        return $datatables->make(true);
    }
}
