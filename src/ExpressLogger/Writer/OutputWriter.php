<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FormatterInterface};

/**
 * Class OutputWriter
 * @package ExpressLogger\Writer
 */
class OutputWriter extends FileWriter
{
    /**
     * OutputWriter constructor.
     * @param FormatterInterface|null $formatter Formatter.
     */
    public function __construct(?FormatterInterface $formatter = null)
    {
        parent::__construct('php://output', $formatter);
    }
}
