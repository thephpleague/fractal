<?php

namespace League\Fractal\Hal;

use League\Fractal\TransformerAbstract;

class CurrieTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'resources'
    ];

    /**
     * Transformation representation of currie OBJ.
     *
     * @param Currie $currie Currie OBJ.
     *
     * @return array Currie OBJ transformed.
     */
    public function transform(Currie $currie)
    {
        return [
            'name' => $currie->getName(),
            'href' => $currie->getHref()
        ];
    }

    /**
     * Include currie resources.
     *
     * @param Currie $currie Resource curries.
     *
     * @return \League\Fractal\Resource\Item Fractal resource item OBJ.
     */
    public function includeResources(Currie $currie)
    {
        return $this->collection($currie->getResources(), new CurrieResourceTransformer($currie->getName()));
    }
}
