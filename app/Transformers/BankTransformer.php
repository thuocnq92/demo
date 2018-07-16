<?php

namespace App\Transformers;

use App\Models\UserBank;
use League\Fractal\TransformerAbstract;
use League\Fractal\Manager;
use League\Fractal;

class BankTransformer extends TransformerAbstract {
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
    public function transform( UserBank $model ) {
        $data = $model->toArray();
        $data['type_name'] = $model->type_name;

        unset($data['created_at']);
        unset($data['updated_at']);

        return $data;
    }
}
