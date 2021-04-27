<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Formatter;

use ExpressLogger\API\FormatterInterface;

/**
 * Class JsonFormatter
 * @package ExpressLogger\Formatters
 */
class JsonPrettyFormatter extends JsonFormatter implements FormatterInterface
{
    protected int $flags =  JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
}
