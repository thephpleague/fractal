<?php

namespace League\Fractal\Hal;

use League\Fractal\TransformerAbstract;

class CurrieResourceTransformer extends TransformerAbstract
{
    protected $currieName;

    public function __construct($currieName)
    {
        $this->currieName = $currieName . '.';
    }

    public function transform(CurrieResource $resource)
    {
        return [
            $this->currieName . $resource->getKey() => $resource->getHref()
        ];
    }
}
