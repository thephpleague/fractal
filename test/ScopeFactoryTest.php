<?php

namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\ScopeFactory;
use PHPUnit\Framework\TestCase;

class ScopeFactoryTest extends TestCase
{
    public function testItImplementsScopeFactoryInterface()
    {
        $this->assertInstanceOf('League\\Fractal\\ScopeFactoryInterface', $this->createSut());
    }

    public function testItCreatesScopes()
    {
        $sut = $this->createSut();

        $manager = $this->createManager();
        $resource = $this->createResource();
        $scopeIdentifier = 'foo_identifier';

        $scope = $sut->createScopeFor($manager, $resource, $scopeIdentifier);

        $this->assertInstanceOf('League\\Fractal\\ScopeInterface', $scope);
        $this->assertSame($resource, $scope->getResource());
        $this->assertSame($scopeIdentifier, $scope->getScopeIdentifier());
    }

    public function testItCreatesScopesWithParent()
    {
        $manager = $this->createManager();

        $scope = new Scope($manager, $this->createResource(), 'parent_identifier');
        $scope->setParentScopes([
            'parent_scope',
        ]);

        $resource = $this->createResource();
        $scopeIdentifier = 'foo_identifier';

        $expectedParentScopes = [
            'parent_scope',
            'parent_identifier',
        ];

        $sut = $this->createSut();
        $scope = $sut->createChildScopeFor($manager, $scope, $resource, $scopeIdentifier);

        $this->assertInstanceOf('League\\Fractal\\ScopeInterface', $scope);
        $this->assertSame($resource, $scope->getResource());
        $this->assertSame($scopeIdentifier, $scope->getScopeIdentifier());
        $this->assertEquals($expectedParentScopes, $scope->getParentScopes());
    }

    /**
     * @return ScopeFactory
     */
    private function createSut()
    {
        return new ScopeFactory();
    }

    /**
     * @return Manager
     */
    private function createManager()
    {
        return $this->getMockBuilder('League\\Fractal\\Manager')->getMock();
    }

    /**
     * @return ResourceInterface
     */
    private function createResource()
    {
        return $this->getMockBuilder('League\\Fractal\\Resource\\ResourceInterface')->getMock();
    }
}
