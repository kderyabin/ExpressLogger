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

class StandardStrategy implements LoggingStrategyInterface
{
    /**
     * @var WriterInterface[]
     */
    protected array $writers = [];

    /**
     * @param WriterInterface[] $writers
     */
    public function setWriters(array $writers): void
    {
        $this->writers = $writers;
    }

    public function process(array $data): void
    {
        foreach ($this->writers as $writer) {
            $writer->write($data);
        }
    }
}
