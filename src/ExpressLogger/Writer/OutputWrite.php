<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FormatterInterface, WriterInterface};

/**
 * Class OutputWrite
 * @package ExpressLogger\Writer
 */
class OutputWrite extends FileWriter
{
    public function __construct(?FormatterInterface $formatter = null)
    {
        parent::__construct('php://output', $formatter);
    }
}
