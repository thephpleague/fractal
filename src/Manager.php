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
use League\Fractal\Serializer\SerializerAbstract;

class Manager
{
    /**
     * Array of scope identifiers for resources to include
     *
     * @var array
     **/
    protected $requestedIncludes = array();

    /**
     * Array containing modifiers as keys and an array value of params
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
     * Upper limit to how many levels of included data are allowed
     *
     * @var integer
     **/
    protected $recursionLimit = 10;

    /**
     * Serializer
     * 
     * @var \League\Fractal\Serializer\SerializerInterface
     **/
    protected $serializer;
    
    /**
     * Create Data
     *
     * Main method to kick this all off. Make a resource then pass it over, and use toArray()
     *
     * @api
     * @param \League\Fractal\Resource\ResourceAbstract $resource
     * @param string $scopeIdentifier
     * @param string $parentScopeInstance
     * @return \League\Fractal\Scope
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
     * @api
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
     * @api
     * @return array
     **/
    public function getRequestedIncludes()
    {
        return $this->requestedIncludes;
    }
    
    /**
     * Get Serializer
     *
     * @api
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
     * @api
     * @param array|string $includes Array or csv string of resources to include
     * @return $this
     **/
    public function parseIncludes($includes)
    {
        // Wipe these before we go again
        $this->requestedIncludes = $this->includeParams = array();

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        if (! is_array($includes)) {
            throw new \InvalidArgumentException(
                'The parseIncludes() method expects a string or an array. '.gettype($includes).' given'
            );
        }

        foreach ($includes as $include) {
            
            list($includeName, $allModifiersStr) = array_pad(explode(':', $include, 2), 2, null);

            // Trim it down to a cool level of recursion
            $includeName = $this->trimToAcceptableRecursionLevel($includeName);

            if (in_array($includeName, $this->requestedIncludes)) {
                continue;
            }
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
     * Set Recursion Limit
     *
     * @api
     * @param \League\Fractal\Serializer\SerializerInterface
     * @return $this
     **/
    public function setRecursionLimit($recursionLimit)
    {
        $this->recursionLimit = $recursionLimit;
        return $this;
    }

    /**
     * Set Serializer
     *
     * @api
     * @param \League\Fractal\Serializer\SerializerAbstract $serializer
     * @return $this
     **/
    public function setSerializer(SerializerAbstract $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Auto-include Parents
     *
     * Look at the requested includes and automatically include the parents if they
     * are not explicitly requested. E.g: [foo, bar.baz] becomes [foo, bar, bar.baz]
     *
     * @internal
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

    /**
     * Trim to Acceptable Recursion Level
     *
     * Strip off any requested resources that are too many levels deep, to avoid DiCaprio being chased
     * by trains or whatever the hell that movie was about.
     *
     * @internal
     **/
    protected function trimToAcceptableRecursionLevel($includeName)
    {
        return implode('.', array_slice(explode('.', $includeName), 0, $this->recursionLimit));
    }
}
