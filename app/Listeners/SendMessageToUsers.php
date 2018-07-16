<?php

namespace App\Listeners;

use App\Events\MessageCreated;
use App\Transformers\MessageTransformer;
use App\Transformers\Serializer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use LRedis;

class SendMessageToUsers
{
    /**
     * @var Manager
     */
    private $fractal;

    /**
     * @var MessageTransformer
     */
    private $messageTransformer;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Manager $fractal, MessageTransformer $messageTransformer, Serializer $serializer)
    {
        $fractal->setSerializer($serializer);
        $this->fractal = $fractal;
        $this->messageTransformer = $messageTransformer;
    }

    /**
     * Handle the event.
     *
     * @param  MessageCreated $event
     * @return void
     */
    public function handle(MessageCreated $event)
    {
        $redis = LRedis::connection();

        // Used transformer
        $message = new Item($event->message, $this->messageTransformer);
        $message = $this->fractal->createData($message)->toArray();

        $message = json_encode($message);

        $redis->publish('message_created', $message);
    }
}
