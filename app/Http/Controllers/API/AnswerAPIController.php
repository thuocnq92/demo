<?php

namespace App\Http\Controllers\API;

use App\Http\Helper\LogToChannels;
use App\Models\Answer;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnswerAPIController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //Log::info('Send answer for question: ' . $request->question_id . ' at ' . $the_moment->format('Y-m-d H:i:s'));
        $user = Auth::user();

        $rule = [
            'question_id' => ['required', 'exists:questions,id',
                Rule::unique('answers')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
            ],
            'answer' => 'required|integer|in:1,2,3,4',
            'answer_time' => 'required|integer'
        ];

        $attribute = [
            'question_id' => 'question',
            'answer' => 'answer',
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $the_moment = Carbon::now();
        $the_moment->timestamp = $request->get('answer_time');

        $question = Question::query()->with('game')->find($request->question_id);
        $question_no_min = Question::query()->where('game_id', $question->game_id)->min('no');

        if ($question->status != Question::STATUS_OPENED || $question->expired_at == null || $question->expired_at->lt($the_moment)) {
            $log = new LogToChannels();
            $log->info('api_error', 'Expired to send answer', [
                'question_status' => $question->status,
                'question_expired_at' => $question->expired_at
            ]);

            return $this->sendError('Expired to send answer', [], 403);
        }

        if ($question->no == $question_no_min) {
            $key = array_search($user->id, $question->game->joiners);
            if ($key === false) {
                $question->game->joiners = array_merge($question->game->joiners, [$user->id]);
                $question->game->save();
            }
        } elseif (!in_array($user->id, $question->game->joiners)) {
            $log = new LogToChannels();
            $log->info('api_error', 'You do not join this game', [
                'user_id' => $user->id,
                'joiners' => json_encode($question->game->joiners)
            ]);

            return $this->sendError('You do not join this game', [], 403);
        }

        $data = $request->all();
        $data['game_id'] = $question ? $question->game_id : 0;
        $data['date'] = Carbon::now();
        $data['result'] = $this->checkResult($request->question_id, $request->answer);

        if ($data['result'] == Answer::WRONG_ANSWER) {
            $joiners = $question->game->joiners;
            $key = array_search($user->id, $joiners);
            if ($key !== false) {
                unset($joiners[$key]);
                $question->game->joiners = array_values($joiners);
                $question->game->save();
            }
        }

        $answer = $user->answers()->create($data);
        $result = [
            'answer' => $answer
        ];

        return $this->sendResponse($result, 'Save answer success');
    }

    /**
     * @param null $question_id
     * @param null $answer
     * @return int
     */
    protected function checkResult($question_id = null, $answer = null)
    {
        if ($question_id === null || $answer === null) {
            return Answer::WRONG_ANSWER;
        }

        $question = Question::query()->find($question_id);

        if (!$question) {
            return Answer::WRONG_ANSWER;
        }

        if ($question->correct_answer == (int)$answer) {
            return Answer::CORRECT_ANSWER;
        }

        return Answer::WRONG_ANSWER;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function use_point(Request $request)
    {
        $user = Auth::user();

        $rule = [
            'question_id' => ['required', 'exists:questions,id'],
        ];

        $attribute = [
            'question_id' => 'question',
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $question = Question::query()->with('game')->find($request->get('question_id'));
        $answer = $question->answers()->where('user_id', $user->id)->first();

        if ($user->fukkatu <= 0) {
            return $this->sendError('out_of_point_heart', [], 400);
        }

        $check_point = $user->answers()->where('game_id', $question->game_id)->where('result', Answer::POINT_HEART)->first();
        if ($check_point) {
            return $this->sendError('access_forbidden', [], 403);
        }

        $user->fukkatu -= 1;
        if ($user->save()) {

            if (!$answer) {
                $answer = new Answer([
                    'question_id' => $question->id,
                    'game_id' => $question->game_id,
                    'user_id' => $user->id,
                    'date' => Carbon::now(),
                    'answer' => 0,
                    'result' => Answer::POINT_HEART,
                ]);
                $answer->save();
            } else {
                //$answer->date = $the_moment;
                $answer->result = Answer::POINT_HEART;
                $answer->save();
            }

            if (!in_array($user->id, $question->game->joiners)) {
                // Add joiner
                $question->game->joiners = array_merge($question->game->joiners, [$user->id]);
                $question->game->save();
            }

            $result = [
                'answer' => $answer
            ];

            return $this->sendResponse($result, 'Used point success');
        }

        return $this->sendError('Internal_server_error', [], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }
}
