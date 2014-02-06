<?php namespace League\PHPUnitCoverageListener\Tests\Hook;

use League\PHPUnitCoverageListener\Collection;
use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Hook\Circle;
use \PHPUnit_Framework_TestCase;

/**
 * Circle hook class test
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class CircleTest extends PHPUnit_Framework_TestCase
{
	public function testIntegrity()
	{
		$circle = new Circle();

		$this->assertInstanceOf('League\PHPUnitCoverageListener\HookInterface', $circle);
	}

	public function testBeforeCollectCallback()
	{
		// Emulate travis environment
		$_ENV['CIRCLECI'] = true;
		$_ENV['CIRCLE_BUILD_NUM'] = 'some-fake-id';

		// Payload data
		$data = new Collection(array(
			'repo_token' => 's3cr3t',
		));

		$this->assertTrue($data->has('repo_token'));

		$circle = new Circle();

		$data = $circle->beforeCollect($data);

		// Repo token will stay
		$this->assertTrue($data->has('repo_token'));

		// And Circle specific keys will be added
		// with above data assigned respectively
		$this->assertTrue($data->has('service_name'));
		$this->assertTrue($data->has('service_job_id'));

		$values = $data->all();

		$this->assertEquals('circle-ci', $values['service_name']);
		$this->assertEquals('some-fake-id', $values['service_job_id']);

		unset($_ENV['CIRCLECI']);
		unset($_ENV['CIRCLE_BUILD_NUM']);

	}

	public function testAfterCollectCallback()
	{
		// Payload data
		$data = new Collection(array(
			'repo_token' => 's3cr3t',
		));

		$this->assertTrue($data->has('repo_token'));

		// Nothing happens on after callback
		$circle = new Circle();

		$data = $circle->afterCollect($data);

		$this->assertTrue($data->has('repo_token'));
	}
}