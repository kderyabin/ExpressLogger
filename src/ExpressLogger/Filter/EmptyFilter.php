<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Filter;

use ExpressLogger\API\FilterInterface;

/**
 * Class EmptyFilter removes empty data (null and empty strings).
 * @package ExpressLogger\Filter
 */
class EmptyFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function filter(array $data)
    {
        return array_filter($data, fn($value) => !($value === '' || $value === null));
    }
}
