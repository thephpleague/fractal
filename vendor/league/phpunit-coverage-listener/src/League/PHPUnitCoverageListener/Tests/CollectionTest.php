<?php namespace League\PHPUnitCoverageListener\Tests;

use League\PHPUnitCoverageListener\Collection;
use \PHPUnit_Framework_TestCase;

/**
 * Collection class test
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class CollectionTest extends PHPUnit_Framework_TestCase
{
	public function testIntegrity()
	{
		$collection = new Collection();

		$this->assertInstanceOf('\IteratorAggregate', $collection);
		$this->assertInstanceOf('\Countable', $collection);
		$this->assertObjectHasAttribute('parameters', $collection);
	}

	public function testIteratorAggregateImplementation()
	{
		$collection = new Collection(array('foo' => 'bar'));

		$iterator = $collection->getIterator();

		$this->assertInstanceOf('\ArrayIterator', $iterator);
	}

	public function testCountableImplementation()
	{
		$collection = new Collection(array_fill(0, 3, null));

		$this->assertCount(3, $collection);
	}

	public function testUtility()
	{
		$collection = new Collection(array('foo' => 'Mr.Foo'));

		$this->assertCount(1, $collection);
		$this->assertTrue($collection->has('foo'));
		$this->assertFalse($collection->has('bar'));

		// Add a parameter
		$collection->add(array('bar' => 'Mr. Bar'));

		$this->assertCount(2, $collection);
		$this->assertTrue($collection->has('foo'));
		$this->assertTrue($collection->has('bar'));

		// Remove a parameter
		$collection->remove('foo');

		$this->assertCount(1, $collection);
		$this->assertFalse($collection->has('foo'));
		$this->assertTrue($collection->has('bar'));

		// Change parameter value
		$collection->set('bar', 'Mr. Not Bar');

		$this->assertEquals('Mr. Not Bar', current($collection->all()));
	}
}