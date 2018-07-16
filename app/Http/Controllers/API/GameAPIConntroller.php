<?php

namespace App\Http\Controllers\API;

use App\Models\Answer;
use App\Models\User;
use App\Transformers\GameTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Transformers\UserShowGameTransformer;
use League\Fractal;
use League\Fractal\Manager;

class GameAPIConntroller extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->sendError('access_forbidden', [], 403);

        $limit = $request->has('limit') ? $request->get('limit') : 10;

        $games = Game::query()->paginate($limit);

        if ($games->isEmpty()) {
            return $this->sendError('No game found!');
        }

        $result = [
            'games' => $games
        ];

        return $this->sendResponse($result, 'Get all game');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Close api
        return $this->sendError('access_forbidden', [], 403);

        $rule = [
            'name' => 'required|unique:games|max:255',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'price' => 'required|numeric|min:0',
        ];

        $attribute = [
            'name' => 'Name',
            'date' => 'Date',
            'price' => 'price',
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $game = Game::createGame($request->all());
        $result = [
            'game' => $game,
        ];

        return $this->sendResponse($result, 'Create new game success!');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return false;
        $game = Game::query()->find($id);

        if (!$game) {
            return $this->sendError('Game not found!');
        }

        $result = [
            'game' => $game
        ];

        return $this->sendResponse($result, 'Get game success!');
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
        return $this->sendError('access_forbidden', [], 403);

        $game = Game::query()->find($id);

        if (!$game) {
            return $this->sendError('Game not found!');
        }

        $rule = [
            'name' => 'sometimes|unique:games|max:255',
            'date' => 'sometimes|date_format:Y-m-d H:i:s',
            'price' => 'sometimes|numeric|min:0',
        ];

        $attribute = [
            'name' => 'Name',
            'date' => 'Date',
            'price' => 'price',
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        $data = $request->all();

        $game->update($data);

        $result = [
            'game' => $game,
        ];

        return $this->sendResponse($result, 'Update success!');
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

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function result($id = 0)
    {
        $game = Game::query()->find($id);

        if (!$game) {
            return $this->sendError('Game not found');
        }

        $total_q = $game->questions()->count();

        $users = User::query()->whereHas('answers', function ($q) use ($id, $total_q) {
            $q->select('user_id', DB::raw('COUNT(*) as no_correct_answer'));
            $q->whereIn('result', [Answer::CORRECT_ANSWER, Answer::POINT_HEART]);
            $q->where('game_id', $id);
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

        return $this->sendResponse($result, 'The users answered correctly all the questions');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function nextGame()
    {
        $now = Carbon::now();

        $game = Game::query()
            ->whereIn('status', [Game::STATUS_OPENED, Game::STATUS_SHOW])
            ->where('date', '<=', $now->format('Y-m-d H:i:s'))
            ->orderBy('date', 'DESC')
            ->first();

        if (empty($game)) {
            // Delay 2 minutes for fix when
            $now_delay = Carbon::now()->subMinute(2);
            $game = Game::query()
                ->whereIn('status', [Game::STATUS_DEFAULT])
                ->where('date', '>=', $now_delay->format('Y-m-d H:i:s'))
                ->orderBy('date', 'ASC')
                ->first();
        }

        if (!$game) {
            return $this->sendError('Next Game not found');
        }

        $answer = $game->answers()->where('user_id', Auth::Id())->where('result', Answer::POINT_HEART)->first();

        $resource = new Fractal\Resource\Item($game, new GameTransformer());
        $fractal = new Manager();
        $users_arr = $fractal->createData($resource)->toArray();

        $result = [
            'game' => $users_arr['data'],
            'used_point' => $answer == null ? 0 : 1,
        ];

        return $this->sendResponse($result, 'Get next game success');
    }
}
