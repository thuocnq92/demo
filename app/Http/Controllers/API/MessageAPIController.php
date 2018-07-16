<?php

namespace App\Http\Controllers\API;

use App\Events\MessageCreated;
use App\Models\Game;
use App\Models\Message;
use App\Transformers\MessageTransformer;
use App\Transformers\Serializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIBaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class MessageAPIController extends APIBaseController
{
    /**
     * @var Manager
     */
    private $fractal;

    /**
     * @var MessageTransformer
     */
    private $messageTransformer;

    public function __construct(Manager $fractal, MessageTransformer $messageTransformer, Serializer $serializer)
    {
        $fractal->setSerializer($serializer);
        $this->fractal = $fractal;
        $this->messageTransformer = $messageTransformer;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = 10;
        $query = Message::with('user');

        // If input have game_id to filter with game_id
        if ($request->has('game_id') && !empty($request->get('game_id'))) {
            $query->where('game_id', $request->get('game_id'));
        }

        // If input have previous_limit to get previous messages
        $show_previous_messages = false;
        if ($request->has('previous_limit') && !empty($request->get('previous_limit'))) {
            $limit = (int)$request->get('previous_limit');
            $show_previous_messages = true;
        }

        // If input have message_id to filter with message_id
        if ($request->has('message_id') && !empty($request->get('message_id'))) {
            if ($show_previous_messages) {
                $query->where('id', '<=', $request->get('message_id'));
            } else {
                $query->where('id', $request->get('message_id'));
            }
        }

        // Order with date is DESC to get message new
        $messages = $query->limit($limit)
            ->orderBy('date', 'DESC')
            ->get();

        // Used transformer
        $messages = new Collection($messages, $this->messageTransformer);
        $messages = $this->fractal->createData($messages)->toArray();

        $result = [
            'messages' => $messages
        ];

        return $this->sendResponse($result, 'Get messages success');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $time_created = Carbon::now();

        $rule = [
            'game_id' => 'required|numeric',
            'message' => 'required|min:1|max:191'
        ];

        $attribute = [
            'game_id' => 'Game ID',
            'message' => 'Message'
        ];

        $validator = Validator::make($request->all(), $rule, $attribute);

        if ($validator->fails()) {
            return $this->sendError('Validation error!', $validator->errors(), 422);
        }

        // Check game to message should have status open and show result
        $game = Game::where('id', $request->get('game_id'))
            ->whereIn('status', [
                Game::STATUS_OPENED,
                Game::STATUS_SHOW
            ])
            ->first();
        if (empty($game)) {
            return $this->sendError('game_not_found');
        }

        $message = Message::create([
            'date' => $time_created->format('Y-m-d H:i:s'),
            'from_user_id' => $user->id,
            'game_id' => $game->id,
            'message' => $request->get('message')
        ]);
        if (empty($message)) {
            return $this->sendError('create_message_error', [], 400);
        }

        // Send message to all user throught socket
        event(new MessageCreated($message));

        $message = new Item($message, $this->messageTransformer);
        $message = $this->fractal->createData($message)->toArray();

        $result = [
            'message' => $message
        ];

        return $this->sendResponse($result, 'Create message success');
    }
}
