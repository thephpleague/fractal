<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class DefaultIncludeBookTransformer extends TransformerAbstract
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
        return $this->item(['c' => 'd'], new GenericAuthorTransformer());
    }
}
