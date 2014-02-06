<?php namespace League\PHPUnitCoverageListener\Hook;

use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Collection;

/**
 * Travis Hook
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class Travis implements HookInterface
{
    /**
     *{@inheritdoc}
     */
    public function beforeCollect(Collection $data)
    {
        // Check for Travis-CI environment
        // if it appears, then assign it respectively
        if (getenv('TRAVIS_JOB_ID') || isset($_ENV['TRAVIS_JOB_ID'])) {
            // Remove repo token
            $data->remove('repo_token');

            // And use travis config
            $travis_job_id = isset($_ENV['TRAVIS_JOB_ID']) ? $_ENV['TRAVIS_JOB_ID'] : getenv('TRAVIS_JOB_ID');
            $data->set('service_name', 'travis-ci');
            $data->set('service_job_id', $travis_job_id);
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