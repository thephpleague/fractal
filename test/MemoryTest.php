<?php

namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\ScopeFactoryInterface;
use League\Fractal\TransformerAbstract;
use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    /**
     * @dataProvider TransformerProvider
     */
    public function testDoesntLeak(ResourceInterface $resource, TransformerAbstract $transformer): void
    {
        $resource->setTransformer($transformer);
        $scopeFactory = \Mockery::mock(ScopeFactoryInterface::class);
        $manager = new Manager($scopeFactory);
        $scope = new Scope($manager, $resource, 'main');
        $subScope = new Scope($manager, new Collection([]), 'sub');
        $scopeFactory->shouldReceive('createScopeFor')->andReturn($scope);
        $scopeFactory->shouldReceive('createChildScopeFor')->andReturn($subScope);

        if ($resource instanceof Primitive) {
            $manager->createData($resource)->transformPrimitiveResource();
        } else {
            $manager->createData($resource)->toArray();
        }
        $this->assertEquals($scope, $transformer->givenScope ?? null);

        if ($transformer->getDefaultIncludes() !== []) {
            $this->assertEquals($resource instanceof Primitive ? null : $scope, $transformer->givenSubScope ?? null);
        }
        $this->assertNull($transformer->getCurrentScope());
    }

    public function TransformerProvider(): \Generator
    {
        $basicTransformer = function () {
            return new class extends TransformerAbstract {
                public $givenScope = null;
                public $givenSubScope = null;

                public function transform($data)
                {
                    $this->givenScope = $this->getCurrentScope();
                    return $data;
                }

                public function includeFoo(): ResourceInterface
                {
                    $this->givenSubScope = $this->getCurrentScope();
                    return new Item(['foo'], $this);
                }
            };
        };
        $resources = [
            new Primitive('foo'),
            new Collection([['a' => 'b'], ['c' => 'd']]),
            new Item(['foo', 'bar', 'baz']),
        ];

        foreach ($resources as $resource) {
            yield [$resource, $basicTransformer()];
        }

        foreach ($resources as $resource) {
            $transformer = $basicTransformer();
            $transformer->setDefaultIncludes(['foo']);
            yield [$resource, $transformer];
        }
    }

    public function testScopeHasNoStrongReferences(): void
    {
        $transformer = new class extends TransformerAbstract {
            public \WeakReference $givenScope;
            public function transform($data)
            {
                $this->givenScope = \WeakReference::create($this->getCurrentScope());
                return $data;
            }
        };

        $resource = new Item(['foo', 'bar', 'baz'], $transformer);
        $manager = new Manager();

        $manager->createData($resource)->toArray();

        gc_collect_cycles();
        $this->assertNull($transformer->getCurrentScope());
        $this->assertNull($transformer->givenScope->get());
    }

}
