<?php namespace League\Fractal;

abstract class ProcessorAbstract
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

    protected function itemResource($data, $processor)
    {
        return new ItemResource($data, $processor);
    }

    protected function collectionResource($data, $processor)
    {
        return new CollectionResource($data, $processor);
    }

    protected function paginatorResource($data, $processor)
    {
        return new PaginatorResource($data, $processor);
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
