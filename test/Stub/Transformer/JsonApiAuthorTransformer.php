<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiAuthorTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'published',
    ];

    public function transform(array $author)
    {
        unset($author['_published']);

        return $author;
    }

    public function includePublished(array $author)
    {
        if (! isset($author['_published'])) {
            return;
        }

        return $this->collection(
            $author['_published'],
            new JsonApiBookTransformer(),
            'books'
        );
    }
}
