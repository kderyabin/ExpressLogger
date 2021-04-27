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
class JsonFormatter implements FormatterInterface
{
    protected int $flags =  JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES;
    protected string $eol = '';

    /**
     * JsonFormatter constructor.
     * @param int|null $flags
     * @param string|null $eol
     */
    public function __construct(?int $flags = null, ?string $eol = null)
    {
        if (null !== $flags) {
            $this->flags = $flags;
        }
        if (null !== $eol) {
            $this->eol = $eol;
        }
    }

    /**
     * Converts a log data to json
     * @param array $data
     * @return string
     */
    public function format(array $data): string
    {
        return json_encode($data, $this->flags) .  $this->eol;
    }
}
