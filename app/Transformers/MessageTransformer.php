<?php

namespace App\Transformers;

use App\Models\Message;
use Illuminate\Support\Facades\App;
use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class MessageTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var  array
     */
    protected $availableIncludes = [];

    /**
     * List of resources to automatically include
     *
     * @var  array
     */
    protected $defaultIncludes = [
        'user'
    ];

    /**
     * Transform object into a generic array
     *
     * @var  object
     */
    public function transform(Message $model)
    {
        $data = [
            'id' => $model->id,
            'date' => $model->date->format('Y-m-d H:i:s'),
            'from_user_id' => $model->from_user_id,
            'game_id' => $model->game_id,
            'message' => $model->message
        ];

        return $data;
    }

    /**
     * @param Message $message
     * @return Item
     */
    public function includeUser(Message $message)
    {
        return $this->item($message->user, App::make(UserShowGameTransformer::class));
    }

}
