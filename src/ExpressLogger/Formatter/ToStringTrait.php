<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Formatter;

use DateTimeInterface;

/**
 * Trait ToStringTrait
 * @package ExpressLogger\Formatter
 */
trait ToStringTrait
{
    public int $jsonFlags = JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES;

    protected string $dateFormat = 'Y-m-d H:i:s.uO';

    /**
     * Converts a value into a string.
     *
     * @param mixed $value
     * @return string
     */
    public function toString($value): string
    {
        if (is_string($value)) {
            return $value;
        }
        $type = gettype($value);
        if ($type === 'object') {
            if (null !== $this->dateFormat && $value instanceof DateTimeInterface) {
                return $value->format($this->dateFormat);
            }
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            }
        }

        if ($type === 'resource') {
            return (string)$value;
        }

        if ($type === 'resource (closed)') {
            return ((string)$value) . ' (closed)';
        }

        return json_encode($value, $this->jsonFlags);
    }

    /**
     * @param string $message
     * @return string
     */
    public function removeEOL(string $message): string
    {
        return strtr($message, ["\r" => '', "\n" => '']);
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @param int $jsonFlags
     */
    public function setJsonFlags(int $jsonFlags): void
    {
        $this->jsonFlags = $jsonFlags;
    }
}
