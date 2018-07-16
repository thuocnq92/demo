<?php

namespace App\Http\Controllers;

use App\Events\GameResultShowed;
use App\Events\UserAnswered;
use App\Models\Answer;
use App\Models\Game;
use App\Models\GameSetting;
use App\Models\Question;
use App\Models\User;
use App\Transformers\GameTransformer;
use App\Transformers\Serializer;
use App\Transformers\UserShowGameTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LRedis;
use League\Fractal;
use League\Fractal\Manager;

class SocketController extends Controller
{
    /**
     * @var Manager
     */
    private $fractal;

    /**
     * @var GameTransformer
     */
    private $gameTransformer;

    public function __construct(Manager $fractal, GameTransformer $gameTransformer, Serializer $serializer)
    {
        $fractal->setSerializer($serializer);
        $this->fractal = $fractal;
        $this->gameTransformer = $gameTransformer;
    }

    public function index()
    {
        return view('socket');
    }

    public function writemessage()
    {
        return view('writemessage');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendMessage(Request $request)
    {
        $redis = LRedis::connection();
        $redis->publish('message', $request->get('message'));

        return redirect('writemessage');
    }

    /**
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function sendEndGame(Request $request)
    {
        if ($request->has('game_id') && !empty($request->get('game_id'))) {
            $game = Game::with(['users' => function ($query) {
                $query->groupBy('user_id');
            }])
                ->where('id', $request->get('game_id'))
                ->first();
            if (empty($game)) {
                return false;
            }
            $next_game = Game::query()
                ->whereIn('status', [Game::STATUS_OPENED, Game::STATUS_DEFAULT, Game::STATUS_SHOW])
                ->orderBy('date', 'ASC')
                ->first();

            $result = [
                'game_id' => $game->id,
                'next_game' => $next_game ? $next_game->toArray() : null
            ];

            $redis = LRedis::connection();

            $data = json_encode($result);

            $redis->publish('end_game', $data);

            // When end game mark user is_played for user can receive fukkatu
            if (count($game->users)) {
                foreach ($game->users as $user) {
                    event(new UserAnswered($user));
                }
            }

            // After send message socket change status game
            $game->status = Game::STATUS_ENDED;
            $game->save();

            $request->session()->flash('success', 'Game id: ' . $game->id . ' is End !');
        }

        return redirect()->route('games.show', ['id' => $game->id, '#qry_end_game_result']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendOpenQuestion(Request $request)
    {
        if ($request->has('question_id') && !empty($request->get('question_id'))) {
            $question = Question::with('game')
                ->where('id', $request->get('question_id'))
                ->first();
            if (empty($question)) {
                $request->session()->flash('danger', 'Question not found !');

                return redirect()->back();
            }

            // Check status game is open and date_notification greater than now
            if ($question->game->status !== Game::STATUS_OPENED) {
                $request->session()->flash('danger', 'Game status wrong !');

                return redirect()->back();
            }

            if ($question->game->is_notified == Game::NOT_NOTIFY) {
                $request->session()->flash('danger', 'Game not send notification !');

                return redirect()->back();
            }

            // Before send message socket change status question
            $question->status = Question::STATUS_OPENED;
            $answer_time = $question->setting ? $question->setting->answer_time : 0;
            $now = Carbon::now();
            $question->expired_at = $now->copy()->addSeconds($answer_time);
            $question->save();

            $redis = LRedis::connection();

            $total_q = Question::query()->where('game_id', $question->game_id)->count();

            $data = json_encode([
                'question' => $question->toArray(),
                'settings' => $question->setting ? $question->setting->toArray() : GameSetting::defaultSetting(),
                'total_question' => $total_q,
                'open_time' => $now->copy()->timestamp,
            ]);

            $redis->publish('open_question', $data);
        }

        return redirect()->route('games.show', ['id' => $question->game->id, '#qry_game_question_' . $question->id]);
    }

    /**
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function sendOpenAnswer(Request $request)
    {
        if ($request->has('question_id') && !empty($request->get('question_id'))) {
            $question = Question::where('id', $request->get('question_id'))->first();
            if (empty($question)) {
                return false;
            }

            $redis = LRedis::connection();

            $answers = $question->answers()
                ->select('answer', DB::raw('Count(*) as count'))
                ->groupBy('answer')
                ->get();

            $result = [
                'question' => $question->toArray(),
                'answers' => $answers->toArray(),
            ];

            $result['question']['correct_answer'] = $question->correct_answer;

            $data = json_encode($result);

            $redis->publish('open_answer', $data);

            // Remove users have not answer
            $user_answers = $question->answers()->pluck('user_id')->toArray();
            $game = $question->game;
            $joiners1 = $joiners2 = $game->joiners;

            if (is_array($joiners1) && !empty($joiners1)) {
                foreach ($joiners1 as $key => $value) {
                    if (!in_array($value, $user_answers)) {
                        unset($joiners2[$key]);
                    }
                }
            }
            $game->joiners = array_values($joiners2);
            $game->save();

            // After send message socket change status question
            $question->status = Question::STATUS_ANSWERED;
            $question->save();
        }

        return redirect()->route('games.show', ['id' => $game->id, '#qry_game_question_' . $question->id]);
    }

    /**
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function showGameResult(Request $request)
    {
        if ($request->has('game_id')) {
            $game_id = $request->get('game_id');
            $game = Game::query()->find($game_id);

            if (!$game) {
                return false;
            }

            $redis = LRedis::connection();

            $total_q = $game->questions()->count();

            $users = User::query()->whereHas('answers', function ($q) use ($game_id, $total_q) {
                $q->select('user_id', DB::raw('COUNT(*) as no_correct_answer'));
                $q->whereIn('result', [Answer::CORRECT_ANSWER, Answer::POINT_HEART]);
                $q->where('game_id', $game_id);
                $q->groupBy('user_id');
                $q->havingRaw('COUNT(*) = ?', [$total_q]);
            })->get();

            // Transformer
            $resource = new Fractal\Resource\Collection($users, new UserShowGameTransformer());
            $fractal = new Manager();
            $users_arr = $fractal->createData($resource)->toArray();

            $total_u = $users->count();
            $price = doubleval($game->price);

            if ($total_u > 0) {
                $bonus = floor($price / $total_u);
            } else {
                $bonus = 0;
            }

            $result = [
                'users' => $users_arr['data'],
                'count_users' => $total_u,
                'bonus' => $bonus,
            ];

            $data = json_encode($result);

            $redis->publish('show_game_result', $data);

            // Add transaction and add money for user
            if ($game->status === Game::STATUS_OPENED) {
                if ($users->isNotEmpty()) {
                    $user_ids = [];
                    foreach ($users as $user) {
                        $user_ids[] = $user->id;
                        Game::createTransaction($user, $bonus, $game->id);
                    }

                    // Calculate seven_days_amount for users have get reward
                    if (count($user_ids)) {
                        event(new GameResultShowed($user_ids));
                    }
                }
                $game->status = Game::STATUS_SHOW;
                $game->save();
            }

            $request->session()->flash('success', 'Game id: ' . $game->id . ' is Show Result !');
        }

        return redirect()->route('games.show', ['id' => $game->id, '#qry_end_game_result']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function backToGameLive(Request $request)
    {
        if ($request->has('game_id')) {
            $game_id = $request->get('game_id');
            $game = Game::query()->find($game_id);

            if (!$game) {
                $request->session()->flash('danger', 'Game not found !');

                return redirect()->back();
            }

            // Only back to game live for game have show_result
            if ($game->status != Game::STATUS_SHOW) {
                $request->session()->flash('danger', 'Game is not show result !');

                return redirect()->back();
            }

            $redis = LRedis::connection();

            // Transformer
            $game_transform = new Fractal\Resource\Item($game, $this->gameTransformer);
            $game_arr = $this->fractal->createData($game_transform)->toArray();

            $result = [
                'game' => $game_arr
            ];

            $data = json_encode($result);

            $redis->publish('back_game_live', $data);

            $request->session()->flash('success', 'Game id: ' . $game->id . ' is Back to live !');
        }

        return redirect()->route('games.show', ['id' => $game->id, '#qry_end_game_result']);
    }

}
