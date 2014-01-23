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

use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Cursor\CursorInterface;

class Scope
{
    protected $availableEmbeds;

    protected $currentScope;

    protected $manager;

    protected $resource;

    protected $parentScopes = array();

    public function __construct(Manager $resourceManager, ResourceInterface $resource, $currentScope = null)
    {
        $this->resourceManager = $resourceManager;
        $this->currentScope = $currentScope;
        $this->resource = $resource;
    }

    public function embedChildScope($scopeIdentifier, $resource)
    {
        return $this->resourceManager->createData($resource, $scopeIdentifier, $this);
    }

    /**
     * Getter for currentScope
     *
     * @return mixed
     */
    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    /**
     * Getter for parentScopes
     *
     * @return mixed
     */
    public function getParentScopes()
    {
        return $this->parentScopes;
    }

    public function isRequested($checkScopeSegment)
    {
        if ($this->parentScopes) {
            $scopeArray = array_slice($this->parentScopes, 1);
            array_push($scopeArray, $this->currentScope, $checkScopeSegment);
        } else {
            $scopeArray = array($checkScopeSegment);
        }

        $scopeString = implode('.', (array) $scopeArray);

        $checkAgainstArray = $this->resourceManager->getRequestedScopes();

        return in_array($scopeString, $checkAgainstArray);
    }

    /**
     * Push a scope identifier into parentScopes
     *
     * @param string $newScope
     *
     * @return int Returns the new number of elements in the array.
     */
    public function pushParentScope($newScope)
    {
        return array_push($this->parentScopes, $newScope);
    }

    /**
     * Setter for parentScopes
     *
     * @param mixed $parentScopes Value to set
     *
     * @return self
     */
    public function setParentScopes($parentScopes)
    {
        $this->parentScopes = $parentScopes;
        return $this;
    }

    /**
     * Convert the current data for this scope to an array
     *
     * @return array
     */
    public function toArray()
    {
        $output = array(
            'data' => $this->runAppropriateTransformer()
        );

        if ($this->availableEmbeds) {
            $output['embeds'] = $this->availableEmbeds;
        }

        if ($this->resource instanceof Collection) {
            $paginator = $this->resource->getPaginator();

            if ($paginator !== null and $paginator instanceof PaginatorInterface) {
                $output['pagination'] = $this->outputPaginator($paginator);
            }

            $cursor = $this->resource->getCursor();

            if ($cursor !== null and $cursor instanceof CursorInterface) {
                $output['cursor'] = $this->outputCursor($cursor);
            }
        }

        return $output;
    }

    /**
     * Convert the current data for this scope to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    protected function fireTransformer($transformer, $data)
    {
        // Fire Main Transformer
        if (is_callable($transformer)) {
            return call_user_func($transformer, $data);
        }

        $processedData = call_user_func(array($transformer, 'transform'), $data);

        // If its an object, process potential embeded resources
        if ($transformer instanceof TransformerAbstract) {
            $embededData = $transformer->processEmbededResources($this, $data);

            if ($embededData !== false) {
                // Push the new embeds in with the main data
                $processedData = array_merge($processedData, $embededData);
            }

            $this->availableEmbeds = $transformer->getAvailableEmbeds();
        }

        return $processedData;
    }

    protected function outputPaginator(PaginatorInterface $paginator)
    {
        $currentPage = (int) $paginator->getCurrentPage();
        $lastPage = (int) $paginator->getLastPage();

        $pagination = array(
            'total' => (int) $paginator->getTotal(),
            'count' => (int) $paginator->count(),
            'per_page' => (int) $paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        );

        $pagination['links'] = array();

        // $paginator->appends(array_except(Request::query(), ['page']));

        if ($currentPage > 1) {
            $pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        return $pagination;
    }

    /**
     * Generates output for cursor adapters. We don't type hint current/next
     * because they can be either a string or a integer.
     *
     * @param  League\Fractal\Cursor\CursorInterface $cursor
     * @return array
     */
    protected function outputCursor(CursorInterface $cursor)
    {
        $cursor = array(
            'current' => $cursor->getCurrent(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        );

        return $cursor;
    }

    protected function runAppropriateTransformer()
    {
        // if's n shit
        if ($this->resource instanceof Item) {
            $data = $this->transformItem();
        } elseif ($this->resource instanceof Collection) {
            $data = $this->transformCollection();
        } else {
            throw new \InvalidArgumentException(
                'Argument $resource should be an instance of Resource\Item or Resource\Collection'
            );
        }

        return $data;
    }

    protected function transformItem()
    {
        $transformer = $this->resource->getTransformer();
        return $this->fireTransformer($transformer, $this->resource->getData());
    }

    protected function transformCollection()
    {
        $transformer = $this->resource->getTransformer();

        $data = array();
        foreach ($this->resource->getData() as $itemData) {
            $data []= $this->fireTransformer($transformer, $itemData);
        }
        return $data;
    }
}
