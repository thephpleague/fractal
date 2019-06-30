<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\SimplePaginationAdapter;
use League\Fractal\Test\TestCase;

class SimplePaginationAdapterTest extends TestCase
{

    public function testBasicFunctionality()
    {
        $pagination = new SimplePaginationAdapter(1, 10, 10, 50, function($page) {
            return "FOO{$page}";
        });

        $this->assertEquals(10, $pagination->getCount());
        $this->assertEquals(1, $pagination->getCurrentPage());
        $this->assertEquals(5, $pagination->getLastPage());
        $this->assertEquals(10, $pagination->getPerPage());
        $this->assertEquals(50, $pagination->getTotal());
        $this->assertEquals('FOO10', $pagination->getUrl(10));
        $this->assertEquals('FOO20', $pagination->getUrl(20));
    }

}
