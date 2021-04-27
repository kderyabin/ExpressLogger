<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\API;

/**
 * Interface FilterCollectionInterface
 * @package Logger\API
 */
interface FilterCollectionInterface
{
    /**
     * Apply available filters to the data.
     * @param array $data Log data to filter
     * @return array|false FALSE if data does not pass a filter otherwise filtered data.
     */
    public function applyFilters(array $data);

    /**
     * Check if the object has filters.
     * @return bool
     */
    public function hasFilters(): bool;

    /**
     * Add filter
     * @param callable $filter
     */
    public function addFilter(callable $filter): void;
}
