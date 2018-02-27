<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiAuthorTransformerWithSingularType extends TransformerAbstract
{
    protected $availableIncludes = [
        'published',
    ];

    public function transform(array $author)
    {
        unset($author['_published']);

        return $author;
    }

    public function includePublished(array $author)
    {
        return $this->collection($author['_published'], new JsonApiBookTransformerWithSingularType(), 'book');
    }
}
