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

    public function createData($resource, $scopeIdentifier = null, $parentScopeInstance = null)
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

    public function getRequestedIncludes()
    {
        return $this->requestedIncludes;
    }

    /**
     * Get Include Params
     *
     * @param $include
     * @return array|null
     */
    public function getIncludeParams($include)
    {
        return isset($this->includeParams[$include]) ? $this->includeParams[$include] : null;
    }

    /**
     * Parse Include String
     *
     * @param array|string $includes List of resources to include
     *
     * @return $this
     */
    public function parseIncludes($includes)
    {
        // Wipe these before we go again
        $this->requestedIncludes = $this->includeParams = array();

        // ([^:]+):(([^\(]+)\(([^\)]+)\),?)+

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
