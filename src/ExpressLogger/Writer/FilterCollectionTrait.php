<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

trait FilterCollectionTrait
{
    /**
     * @var callable[]
     */
    protected array $filters = [];

    /**
     * @param array $data
     * @return array
     */
    public function applyFilters(array $data): array
    {
        if (!$this->hasFilters()) {
            return $data;
        }
        foreach ($this->filters as $filter) {
            $data = call_user_func($filter, $data);
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    public function addFilter($filter): void
    {
        if (is_callable($filter)) {
            $this->filters[] = $filter;
        }
    }
}
