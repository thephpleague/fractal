<?php
/**
 * User: dmitriy.galievskiy
 * Email: dmitriy.galievskiy{at}outlook.com
 * Date: 01.12.2014
 * Time: 14:34
 */

namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericBookWithDefaultResourceKeyTransformer  extends TransformerAbstract
{
	protected $availableIncludes = array(
		'author'
	);

	public function transform(array $book)
	{
		return array(
			'title' => $book['title'],
			'year' => (int) $book['year'],
		);
	}

	public function includeAuthor(array $book)
	{
		if (! isset($book['_author'])) {
			return null;
		}

		return $this->item($book['_author'], new GenericAuthorTransformer);
	}
}
