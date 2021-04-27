<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\API;

use Traversable;

/**
 * Interface FilterCollectionInterface
 * @package ExpressLogger\API
 */
interface FilterCollectionInterface
{
    /**
     * Apply available filters to the data.
     * @param array $data
     * @return array
     */
    public function applyFilters(array $data): array;

    /**
     * Check if the object has filters.
     * @return bool
     */
    public function hasFilters(): bool;

    /**
     * Add filter
     * @param $filter
     */
    public function addFilter($filter): void;
}
