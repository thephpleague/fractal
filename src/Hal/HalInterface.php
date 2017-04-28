<?php

namespace League\Fractal\Hal;

interface HalInterface
{
    const STRUCTURE_KEY = '_links';

    /**
     * Get self link.
     *
     * @return string URL resource for self resource data.
     */
    public function getSelfLink();

    /**
     * Get next link.
     *
     * @return string URL link of next resources data (can be empty).
     */
    public function getNextLink();

    /**
     * Previous link.
     *
     * @return string URL link of previous resources data (can be empty).
     */
    public function getPreviousLink();

    /**
     * @return CurrieResource[]
     */
    public function getCurries();
}
