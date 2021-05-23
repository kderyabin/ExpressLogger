<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Filter;

use ExpressLogger\API\FilterCollectionInterface;
use ExpressLogger\API\FilterInterface;

/**
 * Trait FilterCollectionTrait implements FilterCollectionInterface.
 * @see FilterCollectionInterface
 * @package ExpressLogger\Filter
 */
trait FilterCollectionTrait
{
    /**
     * @var FilterInterface[]
     */
    protected array $filters = [];

    /**
     * @param array $data
     * @return array|false
     */
    public function applyFilters(array $data)
    {
        if (!$this->hasFilters()) {
            return $data;
        }
        foreach ($this->filters as $filter) {
            $data = $filter->filter($data);
            if (!$data) {
                return false;
            }
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

    /**
     * @param FilterInterface|callable $filter
     */
    public function addFilter($filter): void
    {
        $this->filters[] = $filter instanceof FilterInterface ? $filter : new CallbackFilter($filter);
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface[] $filters
     * @return static
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }
}
