<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

use JetBrains\PhpStorm\Pure;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * Manager
 *
 * Not a wildly creative name, but the manager is what a Fractal user will interact
 * with the most. The manager has various configurable options, and allows users
 * to create the "root scope" easily.
 */
class Manager
{
    /**
     * Array of scope identifiers for resources to include.
     *
     * @var array
     */
    protected array $requestedIncludes = [];

    /**
     * Array of scope identifiers for resources to exclude.
     *
     * @var array
     */
    protected array $requestedExcludes = [];

    /**
     * Array of requested fieldsets.
     *
     * @var array
     */
    protected array $requestedFieldsets = [];

    /**
     * Array containing modifiers as keys and an array value of params.
     *
     * @var array
     */
    protected array $includeParams = [];

    /**
     * The character used to separate modifier parameters.
     *
     * @var string
     */
    protected string $paramDelimiter = '|';

    /**
     * Upper limit to how many levels of included data are allowed.
     *
     * @var int
     */
    protected int $recursionLimit = 10;

    /**
     * Serializer.
     *
     * @var SerializerAbstract
     */
    protected SerializerAbstract $serializer;

    /**
     * Factory used to create new configured scopes.
     *
     * @var ScopeFactoryInterface
     */
    private ScopeFactoryInterface|ScopeFactory $scopeFactory;

    public function __construct(ScopeFactoryInterface $scopeFactory = null)
    {
        $this->scopeFactory = $scopeFactory ?: new ScopeFactory();
    }

    /**
     * Create Data.
     *
     * Main method to kick this all off. Make a resource then pass it over, and use toArray()
     *
     * @param ResourceInterface $resource
     * @param string|null       $scopeIdentifier
     * @param Scope             $parentScopeInstance
     *
     * @return Scope
     */
    public function createData(ResourceInterface $resource, string $scopeIdentifier = null, Scope $parentScopeInstance = null): Scope
    {
        if ($parentScopeInstance !== null) {
            return $this->scopeFactory->createChildScopeFor($this, $parentScopeInstance, $resource, $scopeIdentifier);
        }

        return $this->scopeFactory->createScopeFor($this, $resource, $scopeIdentifier);
    }

    /**
     * Get Include Params.
     *
     * @param string $include
     *
     * @return \League\Fractal\ParamBag
     */
    #[Pure] public function getIncludeParams(string $include): ParamBag
    {
        $params = $this->includeParams[$include] ?? [];

        return new ParamBag($params);
    }

    /**
     * Get Requested Includes.
     *
     * @return array
     */
    public function getRequestedIncludes(): array
    {
        return $this->requestedIncludes;
    }

    /**
     * Get Requested Excludes.
     *
     * @return array
     */
    public function getRequestedExcludes(): array
    {
        return $this->requestedExcludes;
    }

    /**
     * Get Serializer.
     *
     * @return SerializerAbstract
     */
    public function getSerializer(): SerializerAbstract
    {
        if (! $this->serializer) {
            $this->setSerializer(new DataArraySerializer());
        }

        return $this->serializer;
    }

    /**
     * Parse Include String.
     *
     * @param array|string $includes Array or csv string of resources to include
     *
     * @return $this
     */
    public function parseIncludes(array|string $includes): static
    {
        // Wipe these before we go again
        $this->requestedIncludes = $this->includeParams = [];
        $subRelations = '';

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        if (! is_array($includes)) {
            throw new \InvalidArgumentException(
                'The parseIncludes() method expects a string or an array. '.gettype($includes).' given'
            );
        }

        foreach ($includes as $include) {
            [$includeName, $allModifiersStr] = array_pad(explode(':', $include, 2), 2, null);
            [$allModifiersStr, $subRelations] = array_pad(explode('.', $allModifiersStr, 2), 2, null);

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

            // Matches multiple instances of 'something(foo|bar|baz)' in the string
            // I guess it ignores : so you could use anything, but probably don't do that
            preg_match_all('/([\w]+)(\(([^\)]+)\))?/', $allModifiersStr, $allModifiersArr);

            // [0] is full matched strings...
            $modifierCount = count($allModifiersArr[0]);

            $modifierArr = [];

            for ($modifierIt = 0; $modifierIt < $modifierCount; $modifierIt++) {
                // [1] is the modifier
                $modifierName = $allModifiersArr[1][$modifierIt];

                // and [3] is delimited params
                $modifierParamStr = $allModifiersArr[3][$modifierIt];

                // Make modifier array key with an array of params as the value
                $modifierArr[$modifierName] = explode($this->paramDelimiter, $modifierParamStr);
            }

            $this->includeParams[$includeName] = $modifierArr;

            if ($subRelations) {
                $this->requestedIncludes[] = $this->trimToAcceptableRecursionLevel($includeName . '.' . $subRelations);
            }
        }

        // This should be optional and public someday, but without it includes would never show up
        $this->autoIncludeParents();

        return $this;
    }

    /**
     * Parse field parameter.
     *
     * @param array $fieldsets Array of fields to include. It must be an array whose keys
     *                         are resource types and values an array or a string
     *                         of the fields to return, separated by a comma
     *
     * @return $this
     */
    public function parseFieldsets(array $fieldsets): static
    {
        $this->requestedFieldsets = [];
        foreach ($fieldsets as $type => $fields) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }

            //Remove empty and repeated fields
            $this->requestedFieldsets[$type] = array_unique(array_filter($fields));
        }
        return $this;
    }

    /**
     * Get requested fieldsets.
     *
     * @return array
     */
    public function getRequestedFieldsets(): array
    {
        return $this->requestedFieldsets;
    }

    /**
     * Get fieldset params for the specified type.
     *
     * @param string $type
     *
     * @return \League\Fractal\ParamBag|null
     */
    #[Pure] public function getFieldset(string $type): ?ParamBag
    {
        return !isset($this->requestedFieldsets[$type]) ?
            null :
            new ParamBag($this->requestedFieldsets[$type]);
    }

    /**
     * Parse Exclude String.
     *
     * @param array|string $excludes Array or csv string of resources to exclude
     *
     * @return $this
     */
    public function parseExcludes(array|string $excludes): static
    {
        $this->requestedExcludes = [];

        if (is_string($excludes)) {
            $excludes = explode(',', $excludes);
        }

        if (! is_array($excludes)) {
            throw new \InvalidArgumentException(
                'The parseExcludes() method expects a string or an array. '.gettype($excludes).' given'
            );
        }

        foreach ($excludes as $excludeName) {
            $excludeName = $this->trimToAcceptableRecursionLevel($excludeName);

            if (in_array($excludeName, $this->requestedExcludes)) {
                continue;
            }

            $this->requestedExcludes[] = $excludeName;
        }

        return $this;
    }

    /**
     * Set Recursion Limit.
     *
     * @param int $recursionLimit
     *
     * @return $this
     */
    public function setRecursionLimit(int $recursionLimit): static
    {
        $this->recursionLimit = $recursionLimit;

        return $this;
    }

    /**
     * Set Serializer
     *
     * @param SerializerAbstract $serializer
     *
     * @return $this
     */
    public function setSerializer(SerializerAbstract $serializer): static
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
     *
     * @return void
     */
    protected function autoIncludeParents(): void
    {
        $parsed = [];

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
     * @param string $includeName
     *
     * @return string
     *@internal
     *
     */
    protected function trimToAcceptableRecursionLevel(string $includeName): string
    {
        return implode('.', array_slice(explode('.', $includeName), 0, $this->recursionLimit));
    }
}
