<?php

namespace League\Fractal;

use League\Fractal\Scope;

abstract class NestedTransformerAbstract implements TransformerInterface
{
    /**
     * The transformer to use 
     * 
     * @var TransformerInterface
     * @access protected
     */
    protected $transformer;

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setTransformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function getAvailableEmbeds()
    {
        return $this->transformer->getAvailableEmbeds();
    }

    public function setAvailableEmbeds($availableEmbeds)
    {
        return $this->transformer->setAvailableEmbeds($availableEmbeds);
    }

    public function getDefaultEmbeds()
    {
        return $this->transformer->getDefaultEmbeds();
    }

    public function setDefaultEmbeds($defaultEmbeds)
    {
        $this->transformer->setDefaultEmbeds();
    }

    public function processEmbededResources(Scope $scope, $data)
    {
        $this->transformer->processEmbeddedResources();
    }

    public function transform($data)
    {
        return $this->transformer->transform($data);
    }

}
