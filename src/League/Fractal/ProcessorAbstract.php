<?php namespace League\Fractal;

abstract class ProcessorAbstract
{
    protected $manager;
    protected $scopeIdentifier;

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    public function getScopeIdentifier()
    {
        return $this->scopeIdentifier;
    }
}
