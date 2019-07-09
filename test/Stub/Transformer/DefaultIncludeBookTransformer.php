<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\Test\Dto\Person;
use League\Fractal\TransformerAbstract;

class DefaultIncludeBookTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'author',
    ];

    public function transform()
    {
        return ['a' => 'b'];
    }

    public function includeAuthor()
    {
        return $this->item(Person::make('Robert Cecil Martin'), new GenericAuthorTransformer());
    }
}
