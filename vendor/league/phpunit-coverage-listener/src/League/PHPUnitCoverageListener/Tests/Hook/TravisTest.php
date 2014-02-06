<?php namespace League\PHPUnitCoverageListener\Tests\Hook;

use League\PHPUnitCoverageListener\Collection;
use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Hook\Travis;
use \PHPUnit_Framework_TestCase;

/**
 * Travis hook class test
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class TravisTest extends PHPUnit_Framework_TestCase
{
	public function testIntegrity()
	{
		$travis = new Travis();

		$this->assertInstanceOf('League\PHPUnitCoverageListener\HookInterface', $travis);
	}

	public function testBeforeCollectCallback()
	{
		// Emulate travis environment
		$_ENV['TRAVIS_JOB_ID'] = 'some-fake-id';

		// Payload data
		$data = new Collection(array(
			'repo_token' => 's3cr3t',
		));

		$this->assertTrue($data->has('repo_token'));

		$travis = new Travis();

		$data = $travis->beforeCollect($data);

		// Repo token will removed
		$this->assertFalse($data->has('repo_token'));

		// And Travis specific keys will be added
		// with above data assigned respectively
		$this->assertTrue($data->has('service_name'));
		$this->assertTrue($data->has('service_job_id'));

		$values = $data->all();

		$this->assertEquals('travis-ci', $values['service_name']);
		$this->assertEquals('some-fake-id', $values['service_job_id']);

		unset($_ENV['TRAVIS_JOB_ID']);

	}

	public function testAfterCollectCallback()
	{
		// Payload data
		$data = new Collection(array(
			'repo_token' => 's3cr3t',
		));

		$this->assertTrue($data->has('repo_token'));

		// Nothing happens on after callback
		$travis = new Travis();

		$data = $travis->afterCollect($data);

		$this->assertTrue($data->has('repo_token'));
	}
}