<?php

namespace League\Fractal\Hal;

use League\Fractal\TransformerAbstract;

/**
 * Class responsible for the Hypertext Application Language (HAL) transformation.
 *
 * @package League\Fractal\Hal
 */
class HalTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'curries'
    ];

    /**
     * Transform data to output.
     *
     * @param HalInterface $resource Links to the current data.
     *
     * @return array List with transformed data.
     */
    public function transform(HalInterface $resource)
    {
        return [
            'self'     => $resource->getSelfLink(),
            'next'     => $resource->getNextLink(),
            'previous' => $resource->getPreviousLink()
        ];
    }

    /**
     * Include resources locations.
     *
     * @param HalInterface $resource Links to the current data.
     *
     * @return \League\Fractal\Resource\Item Fractal resource item OBJ.
     */
    public function includeCurries(HalInterface $resource)
    {
        return $this->collection($resource->getCurries(), new CurrieTransformer());
    }
}
