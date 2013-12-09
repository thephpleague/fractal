<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

use Illuminate\Pagination\Paginator;

class PaginatorResource implements ResourceInterface
{
    protected $paginator;
    protected $transformer;
    protected $data;

    public function __construct(Paginator $paginator, $transformer)
    {
        $this->paginator = $paginator;
        $this->transformer = $transformer;
        $this->data = $paginator->getCollection();
    }
    
    public function getData()
    {
        return $this->data;
    }

    public function getPaginator()
    {
        return $this->paginator;
    }

    public function getTransformer()
    {
        return $this->transformer;
    }
}
