<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class NullIncludeBookTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'author',
    ];

    public function transform()
    {
        return ['a' => 'b'];
    }

    public function includeAuthor()
    {
        return $this->null();
    }
}
