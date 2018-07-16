<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class UserShowGameTransformer extends TransformerAbstract {
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
    public function transform( User $model ) {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'avatar' => $model->avatar
        ];

        return $data;
    }

}
