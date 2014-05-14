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

use League\Fractal\Resource\ResourceInterface;

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
     * Create Data
     *
     * Main method to kick this all off. Make a resource then pass it over, and use toArray()
     *
     * @api
     *
     * @param \League\Fractal\Resource\ResourceInterface $resource
     * @param string|null $scopeIdentifier
     * @param string|null $parentScopeInstance
     *
     * @return Scope
     */
    public function createData(ResourceInterface $resource, $scopeIdentifier = null, $parentScopeInstance = null)
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
     * Get Requested Include
     *
     * @api
     *
     * @return array
     */
    public function getRequestedIncludes()
    {
        return $this->requestedIncludes;
    }

    /**
     * Get Include Params
     *
     * @api
     *
     * @param string $include
     * @return array|null
     */
    public function getIncludeParams($include)
    {
        return isset($this->includeParams[$include]) ? $this->includeParams[$include] : null;
    }

    /**
     * Parse Include String
     *
     * @api
     *
     * @param array|string $includes List of resources to include
     *
     * @return $this
     */
    public function parseIncludes($includes)
    {
        // Wipe these before we go again
        $this->requestedIncludes = $this->includeParams = array();

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        foreach ($includes as $include) {
            
            list ($includeName, $allModifiersStr) = array_pad(explode(':', $include, 2), 2, null);

            $this->requestedIncludes[] = $includeName;

            // No Params? Bored
            if ($allModifiersStr === null) {
                continue;
            }

            preg_match_all('/([\w]+)\(([^\)]+)\)/', $allModifiersStr, $allModifiersArr);

            // They match in threes
            $modifierCount = count($allModifiersArr[0]);

            // There will probably be... some
            $modifierArr = array();

            for ($modifierIt = 0; $modifierIt < $modifierCount; $modifierIt++) {
                $modifierName = $allModifiersArr[1][$modifierIt];
                $modifierParamStr = $allModifiersArr[2][$modifierIt];
                
                $modifierArr[$modifierName] = explode($this->paramDelimiter, $modifierParamStr);
            }

            $this->includeParams[$includeName] = $modifierArr;
        }

        // This should be optional and public someday, but without it includes would never show up
        $this->autoIncludeParents();

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
}
