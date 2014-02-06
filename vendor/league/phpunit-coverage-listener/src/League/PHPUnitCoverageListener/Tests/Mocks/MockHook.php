<?php namespace League\PHPUnitCoverageListener\Tests\Mocks;

use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Collection;

/**
 * Mock Hook
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class MockHook implements HookInterface
{
    /**
     *{@inheritdoc}
     */
    public function beforeCollect(Collection $data)
    {
        return $data;
    }

    /**
     *{@inheritdoc}
     */
    public function afterCollect(Collection $data)
    {
        return $data;
    }
}