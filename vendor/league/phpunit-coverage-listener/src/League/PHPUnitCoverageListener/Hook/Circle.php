<?php namespace League\PHPUnitCoverageListener\Hook;

use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Collection;

/**
 * Circle Hook
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class Circle implements HookInterface
{
    /**
     *{@inheritdoc}
     */
    public function beforeCollect(Collection $data)
    {
        // Check for Circle-CI environment
        // if it appears, then assign it respectively
        if (getenv('CIRCLECI') || isset($_ENV['CIRCLECI'])) {
            // And use circle config
            $circle_job_id = isset($_ENV['CIRCLE_BUILD_NUM']) ? $_ENV['CIRCLE_BUILD_NUM'] : getenv('CIRCLE_BUILD_NUM');
            $data->set('service_name', 'circle-ci');
            $data->set('service_job_id', $circle_job_id);
        }

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