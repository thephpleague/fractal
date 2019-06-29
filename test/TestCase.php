<?php

namespace League\Fractal\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Simple Mockery accessor
     *
     * @param mixed ...$args
     *
     * @return \Mockery\MockInterface
     */
    protected function getMock(...$args): Mockery\MockInterface
    {
        return Mockery::mock(...$args);
    }

    /**
     * Combined and simplified expectedException method
     *
     * @param string $exception
     * @param string|null $message
     * @param bool $regex
     */
    public function expectException(string $exception, string $message = null, bool $regex = false): void
    {
        parent::expectException($exception);

        if ($message) {
            if ($regex) {
                $this->expectExceptionMessageRegExp($message);
            } else {
                $this->expectExceptionMessage($message);
            }
        }
    }
}
