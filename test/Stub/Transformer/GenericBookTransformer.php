<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\Test\Dto\Book;
use League\Fractal\TransformerAbstract;

class GenericBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'author',
    ];

    public function transform(Book $book)
    {
        $data = [
            'title' => $book->title,
            'year' => (int) $book->year,
        ];

        if (! is_null($book->meta)) {
            $data['meta'] = $book->meta;
        }

        return $data;
    }

    public function includeAuthor(Book $book)
    {
        if (is_null($book->author)) {
            return;
        }

        return $this->item($book->author, new GenericAuthorTransformer(), 'author');
    }
}
