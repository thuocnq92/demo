<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Game;
use App\Models\GameSetting;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Transaction;
use App\Models\User;
use App\Transformers\UserShowGameTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Facades\Datatables;
use League\Fractal;
use League\Fractal\Manager;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('games.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('games.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $date_select = '';
        if (isset($input['date'])) {
            $date_select = strftime('%Y-%m-%d', strtotime($input['date']));
            $input['date'] = strftime('%Y-%m-%d %H:%M:%S', strtotime($input['date']));
        }

        if (isset($input['time_start_game']) && !empty($date_select)) {
            $input['time_start_game'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_start_game']));
        }

        if (isset($input['time_notification']) && !empty($date_select)) {
            $input['time_notification'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_notification']));
        }

        if (isset($input['time_notification_2']) && !empty($date_select)) {
            $input['time_notification_2'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_notification_2']));
        }

        if (isset($input['time_notification_3']) && !empty($date_select)) {
            $input['time_notification_3'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_notification_3']));
        }

        if (isset($input['time_notification_4']) && !empty($date_select)) {
            $input['time_notification_4'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_notification_4']));
        }

        if (isset($input['time_notification_5']) && !empty($date_select)) {
            $input['time_notification_5'] = $date_select . ' ' . strftime('%H:%M:%S', strtotime($input['time_notification_5']));
        }

        $rule = [
            'name' => 'required|max:255',
            'content_notification' => 'required|max:255',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'time_start_game' => 'required|date_format:Y-m-d H:i:s|after:date',
            'time_notification' => 'required|date_format:Y-m-d H:i:s',
            'content_notification_2' => 'nullable|max:255',
            'content_notification_3' => 'nullable|max:255',
            'content_notification_4' => 'nullable|max:255',
            'content_notification_5' => 'nullable|max:255',
            'time_notification_2' => 'nullable|date_format:Y-m-d H:i:s',
            'time_notification_3' => 'nullable|date_format:Y-m-d H:i:s',
            'time_notification_4' => 'nullable|date_format:Y-m-d H:i:s',
            'time_notification_5' => 'nullable|date_format:Y-m-d H:i:s',
            'price' => 'required|numeric|min:0|max:2000000000',
        ];

        $attributes = [
            'name' => 'Name',
            'date' => 'Date',
            'content_notification' => 'Content Notification',
            'time_start_game' => 'Time Start Game',
            'time_notification' => 'Time Notification',
            'content_notification_2' => 'Content Notification 2',
            'content_notification_3' => 'Content Notification 3',
            'content_notification_4' => 'Content Notification 4',
            'content_notification_5' => 'Content Notification 5',
            'time_notification_2' => 'Time Notification 2',
            'time_notification_3' => 'Time Notification 3',
            'time_notification_4' => 'Time Notification 4',
            'time_notification_5' => 'Time Notification 5',
            'price' => 'Price',
        ];

        $validator = Validator::make($input, $rule);
        $validator->setAttributeNames($attributes);
        if ($validator->fails()) {

            return redirect()->to('games/add')->withErrors($validator)->withInput();
        }

        // Create a game
        $input['date_notification'] = $input['time_start_game'];
        $game = Game::createGame($input);
        if (empty($game)) {
            $request->session()->flash('danger', 'Create Game error !');

            return redirect()->back();
        }

        // Create game notification
        if (!empty($input['content_notification'])) {
            $game->createNotifications([
                'content' => $input['content_notification'],
                'time' => $input['time_notification']
            ]);

            if (isset($input['time_notification_2'])) {
                $game->createNotifications([
                    'content' => $input['content_notification_2'],
                    'time' => $input['time_notification_2']
                ]);
            }

            if (isset($input['time_notification_3'])) {
                $game->createNotifications([
                    'content' => $input['content_notification_3'],
                    'time' => $input['time_notification_3']
                ]);
            }

            if (isset($input['time_notification_4'])) {
                $game->createNotifications([
                    'content' => $input['content_notification_4'],
                    'time' => $input['time_notification_4']
                ]);
            }

            if (isset($input['time_notification_5'])) {
                $game->createNotifications([
                    'content' => $input['content_notification_5'],
                    'time' => $input['time_notification_5']
                ]);
            }
        }

        $request->session()->flash('success', 'Create game success !');

        return redirect()->to('games');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $game = Game::with('questions', 'notifications')
            ->where('id', $id)
            ->first();
        if (empty($game)) {
            $request->session()->flash('danger', 'Game not found !');

            return redirect()->back();
        }

        $q_opening = $game->questions()->where('status', Question::STATUS_OPENED)
            ->orWhere('status', Question::STATUS_INIT)->orderBy('no', 'ASC')->first();

        $data = [
            'game' => $game,
            'question_opening' => $q_opening
        ];

        return view('games.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $game = Game::with('notifications')
            ->where('id', $id)
            ->first();
        if (empty($game)) {
            $request->session()->flash('danger', 'Game not found !');

            return redirect()->back();
        }

        // Check game is not live
        if ($game->status != Game::STATUS_DEFAULT) {
            $request->session()->flash('danger', 'Only delete for games not start !');

            return redirect()->back();
        }

        // Remove notification before delete game
        foreach ($game->notifications as $notification) {
            $notification->delete();
        }
        $game->delete();

        $request->session()->flash('success', 'Delete game success !');

        return redirect()->to('games');
    }

    /**
     * @param $id
     * @param $notification_id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove_notification($id, $notification_id, Request $request)
    {
        $notification = Notification::where('game_id', $id)
            ->where('id', $notification_id)
            ->where('is_sent', Notification::IS_NOT_SENT)
            ->first();
        if (empty($notification)) {
            $request->session()->flash('danger', 'Notification not exist !');

            return redirect()->back();
        }

        $notification->delete();

        // If not have notification set game is_notified
        Notification::checkGameNotificationSented($id);

        $request->session()->flash('success', 'Delete notification success !');

        return redirect()->to(route('games.show', ['id' => $id]));
    }

    /**
     * @param $id
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function add_question($id, Request $request)
    {
        $game = Game::with('questions')
            ->where('id', $id)
            ->where('status', Game::STATUS_DEFAULT)
            ->first();
        if (empty($game)) {
            $request->session()->flash('danger', 'Game not found !');

            return redirect()->back();
        }

        $input = $request->all();

        $rules = [
            'question' => 'required|max:255',
            'answer1' => 'required|max:255',
            'answer2' => 'required|max:255',
            'answer3' => 'required|max:255',
//            'answer4' => 'nullable|max:255',
            'correct_answer' => 'required|numeric|between:1,3'
        ];

        $attributes = [
            'question' => 'Question',
            'answer1' => 'Answer 1',
            'answer2' => 'Answer 2',
            'answer3' => 'Answer 3',
//            'answer4' => 'Answer 4',
            'correct_answer' => 'Correct Answer'
        ];

        $validator = Validator::make($input, $rules);
        $validator->setAttributeNames($attributes);
        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator);
        }

        // Get max question_no
        $question_no = 1;
        if ($game->questions->count()) {
            $last_question = Question::where('game_id', $game->id)
                ->orderBy('no', 'desc')
                ->first();
            if ($last_question) {
                $question_no = $last_question->no + 1;
            }
        }

        // Insert question to game
        $question = $game->questions()->create([
            'no' => $question_no,
            'question' => $input['question'],
            'answer1' => $input['answer1'],
            'answer2' => $input['answer2'],
            'answer3' => $input['answer3'],
//            'answer4' => $input['answer4'],
            'correct_answer' => $input['correct_answer'],
        ]);

        if (empty($question)) {
            $request->session()->flash('danger', 'Create question error !');

            return redirect()->back();
        }

        // If create new question to create new setting for question and game
        if ($question && $game) {
            $game_setting = GameSetting::create([
                'game_id' => $game->id,
                'question_id' => $question->id,
                'answer_time' => GameSetting::ANSWER_TIME,
                'correct_answer_time' => GameSetting::CORRECT_ANSWER_TIME,
                'wrong_answer_time' => GameSetting::WRONG_ANSWER_TIME,
                'point_heart_time' => GameSetting::POINT_HEART_TIME,
                'no_point_heart_time' => GameSetting::NO_POINT_HEART_TIME,
                'show_answer_again_time' => GameSetting::SHOW_ANSWER_AGAIN_TIME,
                'view_mode_time' => GameSetting::VIEW_MODE_TIME,
                'show_result_time' => GameSetting::SHOW_RESULT_TIME
            ]);

            if (empty($game_setting)) {
                $request->session()->flash('danger', 'Create setting for game error !');

                return redirect()->back();
            }
        }

        $request->session()->flash('success', 'Create question success !');

        return redirect()->route('games.show', ['id' => $game->id]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function gameData(Request $request)
    {
        $query = Game::withCount('questions');
        $datatables = Datatables::of($query);

        $datatables->addColumn('id', function ($game) {
            return '<a href="' . route('games.show', ['id' => $game->id]) . '">' . $game->id . '</a>';
        })->addColumn('date', function ($game) {
            return $game->date->format('Y/m/d');
        })->addColumn('time_game', function ($game) {
            return $game->date->format('H:i');
        })->addColumn('time_notification', function ($game) {
            return empty($game->date_notification) ? '' : $game->date_notification->format('H:i');
        })->addColumn('is_notified', function ($game) {
            return $game->is_notified == Game::IS_NOTIFIED ? 'true' : 'false';
        })->addColumn('total_question', function ($game) {
            return $game->questions_count;
        })->addColumn('price', function ($game) {
            return '¥' . $game->price;
        })->addColumn('stream_link', function ($game) {
            return env('HOST_STREAM', 'rtmp://13.231.12.160/live1/');
        })->addColumn('live_code', function ($game) {
            return $game->live_code;
        })->addColumn('status', function ($game) {
            $status = '';
            switch ($game->status) {
                case Game::STATUS_DEFAULT:
                    $status = 'Init';
                    break;
                case Game::STATUS_OPENED:
                    $status = 'Opened';
                    break;
                case Game::STATUS_SHOW:
                    $status = 'Show Result';
                    break;
                case Game::STATUS_ENDED:
                    $status = 'Ended';
                    break;
            }

            return $status;
        })->addColumn('actions', function ($game) {
            $button_delete = '<button type="button" 
            class="btn btn-flat btn-danger delete-game"
            data-toggle="modal"
            data-target="#modal-delete-game" 
            data-game_id="' . $game->id . '">
            <i class="fa fa-trash"></i> 
            Delete
            </button>';

            $button_view = '<a class="btn btn-flat btn-info"
             href="' . route('games.show', ['id' => $game->id]) . '">
             <i class="fa fa-eye"></i> 
             View
             </a>';

            return $button_view . ' ' . $button_delete;
        })->rawColumns(['id', 'actions']);

        // Filter
        $datatables->filter(function ($query) use ($request) {
            if ($request->has('game_no')) {
                $query->where('id', $request->get('game_no'));
            }

            if ($request->has('game_date')) {
                $date = Carbon::createFromFormat('Y-m-d', $request->get('game_date'));
                $query->whereDate('date', $date->format('Y-m-d'));
            }

            return $query;
        });

// Order
//        $datatables->order(function ($query) use ($request) {
//
//            // Get sort with total_question
//            $input = $request->all();
//            if (isset($input['order']) && count($input['order']) && isset($input['columns']) && count($input['columns'])) {
//                $sort = null;
//                $index_total_question = null;
//                foreach ($input['columns'] as $index => $column) {
//                    if ($column['name'] === 'total_question') {
//                        $index_total_question = $index;
//                    }
//                }
//
//                if ($index_total_question) {
//                    foreach ($input['order'] as $order) {
//                        if ($order['column'] == $index_total_question) {
//                            $sort = $order['dir'];
//                        }
//                    }
//                }
//
//                if ($sort) {
//                    return $query->orderBy('questions_count', $sort);
//                }
//            }
//        });

        $datatables->orderColumn('id', '-id $1')
            ->orderColumn('date', '-date $1')
            ->orderColumn('time_game', '-date $1')
            ->orderColumn('price', '-price $1');

        return $datatables->make(true);
    }

    public
    function list_users(Request $request)
    {
        $input = $request->all();
        $rules = [
            'filter_game_no' => 'nullable|numeric',
            'filter_game_date' => 'nullable|date_format:"Y-m-d"'
        ];

        $attributes = [
            'filter_game_no' => 'Game No',
            'filter_game_date' => 'Game Date'
        ];

        $validator = Validator::make($input, $rules, $attributes);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator);
        }
    }

    public function result($id, Request $request)
    {
        $game = Game::query()->where('status', Game::STATUS_ENDED)->find($id);

        if (!$game) {
            $request->session()->flash('danger', 'Game not found!');
            return redirect()->back();
        }

        $total_q = $game->questions()->count();

        $total_users = User::query()->with('referrer')->whereHas('answers', function ($q) use ($id, $total_q) {
            $q->where('game_id', $id);
        })->count();

        $users = User::query()->whereHas('answers', function ($q) use ($id, $total_q) {
            $q->select('user_id', DB::raw('COUNT(*) as no_correct_answer'));
            $q->whereIn('result', [Answer::CORRECT_ANSWER, Answer::POINT_HEART]);
            $q->where('game_id', $id);
            $q->groupBy('user_id');
            $q->havingRaw('COUNT(*) = ?', [$total_q]);
        })->withCount(['transactions' => function ($q) {
            $q->where('type', Transaction::TYPE_RECEIVE_MONEY);
        }]);

        $right_users = $users->count();

        $users = $users->paginate(12);

        $price = doubleval($game->price);

        if ($right_users > 0) {
            $bonus = floor($price / $right_users);
        } else {
            $bonus = 0;
        }

        $result = [
            'users' => $users,
            'count_users' => $total_users,
            'right_users' => $right_users,
            'bonus' => $bonus,
            'game' => $game
        ];

        return view('games.result', $result);
    }

}
