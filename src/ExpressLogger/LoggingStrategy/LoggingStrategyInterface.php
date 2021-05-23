<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\LoggingStrategy;

use ExpressLogger\API\WriterInterface;

/**
 * Interface LoggingStrategyInterface
 * @package ExpressLogger\LoggingStrategy
 */
interface LoggingStrategyInterface
{
    /**
     * @param WriterInterface[] $writers
     */
    public function setWriters(array $writers): void;

    /**
     * @param array $data
     */
    public function process(array $data): void;
}
