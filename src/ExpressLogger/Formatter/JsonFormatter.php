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
 * @package ExpressLogger\Formatter
 */
class JsonFormatter implements FormatterInterface
{
    /**
     * @see json_encode flags param (https://www.php.net/manual/en/function.json-encode.php).
     * @var int|string
     */
    protected int $flags =  JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES;
    /**
     * End line to append to generated json.
     * @var string
     */
    protected string $eol = '';
    /**
     * "depth" parameter for json_encode function.
     * @var int
     */
    protected int $depth = 5;

    /**
     * JsonFormatter constructor.
     * @param int|null $flags json_encode flags param
     * @param string|null $eol End line to append to generated json content.
     * @param int|null $depth   json_encode depth param
     */
    public function __construct(?int $flags = null, ?string $eol = null, ?int $depth = null)
    {
        if (null !== $flags) {
            $this->setFlags($flags);
        }
        if (null !== $eol) {
            $this->setEol($eol);
        }

        if (null !== $depth) {
            $this->setDepth($depth);
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

    /**
     * @return int|string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param int|string $flags
     * @return JsonFormatter
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @return string
     */
    public function getEol(): string
    {
        return $this->eol;
    }

    /**
     * @param string $eol
     * @return JsonFormatter
     */
    public function setEol(string $eol): JsonFormatter
    {
        $this->eol = $eol;
        return $this;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     * @return JsonFormatter
     */
    public function setDepth(int $depth): JsonFormatter
    {
        $this->depth = $depth;
        return $this;
    }
}
