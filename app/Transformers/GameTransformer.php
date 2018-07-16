<?php

namespace App\Transformers;

use App\Models\Game;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use League\Fractal\Manager;
use League\Fractal;

class GameTransformer extends TransformerAbstract
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
    protected $defaultIncludes = [];

    /**
     * Transform object into a generic array
     *
     * @var  object
     */
    public function transform(Game $model)
    {
        $data = $model->toArray();
        $now = Carbon::now();

        if ($model->date->gt($now)) {
            unset($data['live_code']);
            unset($data['stream_link']);
        }

        return $data;
    }
}
