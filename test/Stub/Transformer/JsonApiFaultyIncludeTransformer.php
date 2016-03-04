<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiFaultyIncludeTransformer extends TransformerAbstract
{
  protected $availableIncludes = [
    'author',
    'co-author',
  ];

  public function transform(array $book)
  {
    $book['year'] = (int) $book['year'];
    unset($book['_author']);
    unset($book['_co_author']);

    return $book;
  }

  public function includeAuthor(array $book)
  {
    if (!array_key_exists('_author', $book)) {
      return;
    }

    if ($book['_author'] === null) {
      // This should return $this->null() to work properly,
      // but is set to return nothing for testing the exception.
      return;
    }

    return $this->item($book['_author'], new JsonApiAuthorTransformer(), 'people');
  }

  public function includeCoAuthor(array $book)
  {
    if (!array_key_exists('_co_author', $book)) {
      return;
    }

    if ($book['_co_author'] === null) {
      return $this->null();
    }

    return $this->item($book['_co_author'], new JsonApiAuthorTransformer(), 'people');
  }
}
