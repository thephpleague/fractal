<?php

namespace League\Fractal\Test\Stub;

class SimpleTraversable implements \Iterator
{
    private $list;
    private $keys;
    private $cursor = 0;

    public function __construct(array $list)
    {
        $this->list = array_values($list);
        $this->keys = array_keys($list);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->list[$this->cursor];
    }

    public function next(): void
    {
        $this->cursor++;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->keys[$this->cursor];
    }

    public function valid(): bool
    {
        return isset($this->list[$this->cursor]);
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }
}
