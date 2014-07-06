<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class DefaultIncludeBookTransformer extends TransformerAbstract
{
    protected $defaultIncludes = array(
        'author'
    );

    public function transform()
    {
        return array('a' => 'b');
    }

    public function includeAuthor()
    {
        return $this->item(array('c' => 'd'), new GenericAuthorTransformer);
    }
}
