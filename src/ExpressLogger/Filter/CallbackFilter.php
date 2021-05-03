<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Filter;

use ExpressLogger\API\FilterInterface;

/**
 * Class CallbackFilter allows to use a callable as a filter.
 * @package ExpressLogger\Filter
 */
class CallbackFilter implements FilterInterface
{
    /** @var callable */
    protected $callback;

    /**
     * CallbackFilter constructor.
     * @param callable $callback Function to execute.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /** @inheritDoc */
    public function filter(array $data)
    {
        return call_user_func($this->callback, $data);
    }
}
