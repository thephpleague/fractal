<?php

declare(strict_types=1);

namespace League\Fractal\Transformer;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;

trait ResourceCreateTrait
{
    /**
     * Create a new primitive resource object.
     *
     * @param mixed                        $data
     * @param callable|null                $transformer
     */
    protected function primitive($data, ?callable $transformer = null, ?string $resourceKey = null): Primitive
    {
        return new Primitive($data, $transformer, $resourceKey);
    }

    /**
     * Create a new item resource object.
     *
     * @param mixed                        $data
     * @param TransformerAbstract|callable $transformer
     */
    protected function item($data, $transformer, ?string $resourceKey = null): Item
    {
        return new Item($data, $transformer, $resourceKey);
    }

    /**
     * Create a new collection resource object.
     *
     * @param mixed                        $data
     * @param TransformerAbstract|callable $transformer
     */
    protected function collection($data, $transformer, ?string $resourceKey = null): Collection
    {
        return new Collection($data, $transformer, $resourceKey);
    }

    /**
     * Create a new null resource object.
     */
    protected function null(): NullResource
    {
        return new NullResource();
    }
}
