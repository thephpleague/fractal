<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class PrimitiveIncludeBookTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'price'
    ];

    public function transform()
    {
        return ['a' => 'b'];
    }

    public function includePrice(array $book)
    {
        return $this->primitive($book['price'], function ($price) {return (int) $price;});
    }
}
