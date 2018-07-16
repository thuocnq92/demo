<?php

namespace App\Http\Controllers\API;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionAPIController extends APIBaseController
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Close api
        return false;

        $rule = [
            'game_id' => 'required|exists:games,id',
            'no' => ['required', 'integer', 'min:1',
                Rule::unique('questions')->where(function ($query) use ($request) {
                    $query->where('game_id', $request->game_id);
                })
            ],
            'question' => 'required',
            'answer1' => 'required',
            'answer2' => 'required',
            'answer3' => 'required',
            'answer4' => 'required',
            'correct_answer' => 'required|integer|in:1,2,3,4',
        ];

        $attribute = [
            'game_id' => 'game',
            'no' => 'order',
            'question' => 'question',
            'answer1' => 'answer 01',
            'answer2' => 'answer 02',
            'answer3' => 'answer 03',
            'answer4' => 'answer 04',
            'correct_answer' => 'correct answer'
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $question = Question::create($request->all());
        $result = [
            'question' => $question,
        ];

        return $this->sendResponse($result, 'Create new question success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Close api
        return false;

        $question = Question::query()->find($id);

        if (!$question) {
            return $this->sendError('The question not found');
        }

        $result = [
            'question' => $question
        ];

        return $this->sendResponse($result, 'Get question success');

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
        // Close api
        return false;

        $question = Question::query()->find($id);

        if(!$question) {
            return $this->sendError('Question not found!');
        }

        $rule = [
            'game_id' => 'sometimes|exists:games,id',
            'no' => ['sometimes', 'integer', 'min:1',
                Rule::unique('questions')->where(function ($query) use ($request, $question) {
                    $game_id = $request->has('game_id') ? $request->game_id : $question->game_id;
                    $query->where('game_id', $game_id);
                })->ignore($question->id)
            ],
            'correct_answer' => 'sometimes|integer|in:1,2,3,4',
        ];

        $attribute = [
            'game_id' => 'game',
            'no' => 'order',
            'question' => 'question',
            'answer1' => 'answer 01',
            'answer2' => 'answer 02',
            'answer3' => 'answer 03',
            'answer4' => 'answer 04',
            'correct_answer' => 'correct answer'
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $question->update($request->all());
        $result = [
            'question' => $question,
        ];

        return $this->sendResponse($result, 'Update question success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function result($id) {
        return false;
        $question = Question::query()->find($id);

        if (!$question){
            return $this->sendError('Question not found');
        }

        $answers = $question->answers()
            ->select('answer', DB::raw('Count(*) as count'))
            ->groupBy('answer')
            ->get();

        $result = [
            'answers' => $answers,
            'correct_answer' => $question->correct_answer
        ];

        return $this->sendResponse($result, 'Get question\'s result success');
    }
}
