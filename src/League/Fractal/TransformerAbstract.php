<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

abstract class TransformerAbstract
{
    protected $availableEmbeds;
    protected $manager;
    protected $scopeIdentifier;

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager($manager)
    {
        $this->manager = $manager;
        return $this;
    }

    public function getScopeIdentifier()
    {
        return $this->scopeIdentifier;
    }

    protected function itemResource($data, $transformer)
    {
        return new ItemResource($data, $transformer);
    }

    protected function collectionResource($data, $transformer)
    {
        return new CollectionResource($data, $transformer);
    }

    protected function paginatorResource($data, $transformer)
    {
        return new PaginatorResource($data, $transformer);
    }

    public function processEmbededResources(Scope $scope, $data)
    {
        if ($this->availableEmbeds === null) {
            return false;
        }

        $embededData = array();

        foreach ($this->availableEmbeds as $potentialEmbed) {
            if (! $scope->isRequested($potentialEmbed)) {
                continue;
            }

            $methodName = 'embed'.ucfirst($potentialEmbed);
            if (! method_exists($this, $methodName)) {
                throw new \BadMethodCallException(sprintf(
                    'Call to undefined method %s::%s()',
                    get_called_class($this),
                    $methodName
                ));
            }

            $resource = call_user_func(array($this, $methodName), $data);

            $embededData[$potentialEmbed] = $scope->embedChildScope($potentialEmbed, $resource);
        }

        return $embededData;
    }
}
