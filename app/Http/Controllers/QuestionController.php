<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Game::query()->withCount('questions')->orderBy('date');

        if ($request->has('game_id')) {
            $query->where('id', $request->game_id);
        }

        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('date', '=', $date->format('Y-m-d'));
        }

        $games = $query->paginate(5);

        $data = [
            'games' => $games,
            //'questions' => $questions,
        ];

        return view('questions.index', $data);
    }

    public function getQuestions(Request $request)
    {
        $questions = Question::where('game_id', $request->game_id)->get();

        $data = [
            'questions' => $questions
        ];

        return view('questions.questions_table', $data);
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
        //
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check exist question_id and game not opened
        $question = Question::whereHas('game', function ($q) {
            $q->where('status', Game::STATUS_DEFAULT);
        })
            ->where('id', $id)
            ->first();
        if (empty($question)) {
            $request->session()->flash('danger', 'Question not found !');

            return redirect()->back();
        }

        $input = $request->all();

        $rules = [
            'question' => 'required|max:255',
            'answer1' => 'required|max:255',
            'answer2' => 'required|max:255',
            'answer3' => 'required|max:255',
//            'answer4' => 'required|max:255',
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

        // Update question
        $question->question = $input['question'];
        $question->answer1 = $input['answer1'];
        $question->answer2 = $input['answer2'];
        $question->answer3 = $input['answer3'];
//        $question->answer4 = $input['answer4'];
        $question->correct_answer = $input['correct_answer'];

        $question->save();

        if (empty($question)) {
            $request->session()->flash('danger', 'Update question error !');

            return redirect()->back();
        }

        $request->session()->flash('success', 'Update question success !');

        return redirect()->route('games.show', ['id' => $question->game->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        // Check exist question_id and game not opened
        $question = Question::whereHas('game', function ($q) {
            $q->where('status', Game::STATUS_DEFAULT);
        })
            ->where('id', $id)
            ->first();
        if (empty($question)) {
            $request->session()->flash('danger', 'Question not found !');

            return redirect()->back();
        }

        $question->delete();

        $request->session()->flash('success', 'Delete question success !');

        return redirect()->route('games.show', ['id' => $question->game->id]);
    }
}
