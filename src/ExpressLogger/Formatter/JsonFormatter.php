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
 * @package Logger\Formatters
 */
class JsonFormatter implements FormatterInterface
{
    protected int $flags =  JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES;
    protected string $eol = '';
    protected int $depth = 5;

    /**
     * JsonFormatter constructor.
     * @param int|null $flags See json_encode flags param (https://www.php.net/manual/en/function.json-encode.php).
     * @param string|null $eol End of line to append to generated json content/
     * @param int|null $depth   See json_encode depth param (https://www.php.net/manual/en/function.json-encode.php).
     */
    public function __construct(?int $flags = null, ?string $eol = null, ?int $depth = null)
    {
        if (null !== $flags) {
            $this->flags = $flags;
        }
        if (null !== $eol) {
            $this->eol = $eol;
        }

        if (null !== $depth) {
            $this->depth = $depth;
        }
    }

    /**
     * Converts a log data to json
     * @param array $data
     * @return string
     */
    public function format(array $data): string
    {
        return json_encode($data, $this->flags, $this->depth) .  $this->eol;
    }
}
