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

use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\SerializerInterface;

class Manager
{
    /**
     * Requested Scopes
     *
     * @var array
     **/
    protected $requestedIncludes = array();

    /**
     * Requested Params
     *
     * @var array
     **/
    protected $includeParams = array();

    /**
     * The character used to separate modifier parameters
     *
     * @var string
     **/
    protected $paramDelimiter = '|';

    /**
     * Serializer
     * 
     * @var \League\Fractal\Serializer\SerializerInterface
     **/
    protected $serializer;
    
    /**
     * Get Include Params
     *
     * @param \League\Fractal\Resource\ResourceAbstract $resource
     * @param string $scopeIdentifier
     * @param string $parentScopeInstance
     * @return array|null
     **/
    public function createData(ResourceAbstract $resource, $scopeIdentifier = null, $parentScopeInstance = null)
    {
        $scopeInstance = new Scope($this, $resource, $scopeIdentifier);

        // Update scope history
        if ($parentScopeInstance !== null) {

            // This will be the new children list of parents (parents parents, plus the parent)
            $scopeArray = $parentScopeInstance->getParentScopes();
            $scopeArray[] = $parentScopeInstance->getCurrentScope();

            $scopeInstance->setParentScopes($scopeArray);
        }

        return $scopeInstance;
    }

    /**
     * Get Include Params
     *
     * @param string $include
     * @return array|null
     **/
    public function getIncludeParams($include)
    {
        return isset($this->includeParams[$include]) ? $this->includeParams[$include] : null;
    }
    
    /**
     * Get Requested Includes
     *
     * @return array
     **/
    public function getRequestedIncludes()
    {
        return $this->requestedIncludes;
    }
    
    /**
     * Get Serializer
     *
     * @return $this
     **/
    public function getSerializer()
    {
        if (! $this->serializer) {
            $this->setSerializer(new DataArraySerializer);
        }

        return $this->serializer;
    }

    /**
     * Parse Include String
     *
     * @param array|string $includes List of resources to include
     *
     * @return $this
     **/
    public function parseIncludes($includes)
    {
        // Wipe these before we go again
        $this->requestedIncludes = $this->includeParams = array();

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        foreach ($includes as $include) {
            
            list($includeName, $allModifiersStr) = array_pad(explode(':', $include, 2), 2, null);

            $this->requestedIncludes[] = $includeName;

            // No Params? Bored
            if ($allModifiersStr === null) {
                continue;
            }

            // Matches multiple instances of 'something(foo,bar,baz)' in the string
            // I guess it ignores : so you could use anything, but probably dont do that
            preg_match_all('/([\w]+)\(([^\)]+)\)/', $allModifiersStr, $allModifiersArr);

            // [0] is full matched strings...
            $modifierCount = count($allModifiersArr[0]);

            $modifierArr = array();

            for ($modifierIt = 0; $modifierIt < $modifierCount; $modifierIt++) {
                
                // [1] is the modifier
                $modifierName = $allModifiersArr[1][$modifierIt];

                // and [2] is delimited params
                $modifierParamStr = $allModifiersArr[2][$modifierIt];
                
                // Make modifier array key with an array of params as the value
                $modifierArr[$modifierName] = explode($this->paramDelimiter, $modifierParamStr);
            }

            $this->includeParams[$includeName] = $modifierArr;
        }

        // This should be optional and public someday, but without it includes would never show up
        $this->autoIncludeParents();

        return $this;
    }

    /**
     * Set Serializer
     *
     * @param \League\Fractal\Serializer\SerializerInterface
     *
     * @return $this
     **/
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Auto-include Parents
     * Look at the requested includes and automatically include the parents if they 
     * are not explicitly requested. E.g: [foo, bar.baz] becomes [foo, bar, bar.baz]
     **/
    protected function autoIncludeParents()
    {
        $parsed = array();

        foreach ($this->requestedIncludes as $include) {
            $nested = explode('.', $include);

            $part = array_shift($nested);
            $parsed[] = $part;

            while (count($nested) > 0) {
                $part .= '.'.array_shift($nested);
                $parsed[] = $part;
            }
        }

        $this->requestedIncludes = array_values(array_unique($parsed));
    }
}
