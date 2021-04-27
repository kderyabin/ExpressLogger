<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace ExpressLogger\API;

interface FormatterInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function format(array $data): string;
}
