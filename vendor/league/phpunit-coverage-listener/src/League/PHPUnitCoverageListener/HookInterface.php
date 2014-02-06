<?php namespace League\PHPUnitCoverageListener;

use League\PHPUnitCoverageListener\Collection;

/**
 * PHPUnit hook interface
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

interface HookInterface
{
	/**
     * Before collect point
     *
     * @param Collection
     * @return Collection
     */
    public function beforeCollect(Collection $data);

    /**
     * After collect callback
     *
     * @param Collection
     * @return Collection
     */
    public function afterCollect(Collection $data);
}