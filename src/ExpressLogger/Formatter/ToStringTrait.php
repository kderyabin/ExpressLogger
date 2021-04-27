<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Formatter;

/**
 * Trait ToStringTrait
 * @package Logger\Formatters
 */
trait ToStringTrait
{
    public $jsonFlags = JSON_ERROR_NONE | JSON_UNESCAPED_SLASHES;

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
        switch (gettype($value)) {
            case 'object':
                if (null !== $this->dateFormat && $value instanceof \DateTimeInterface) {
                    return $value->format($this->dateFormat);
                }
                if ($value instanceof \Throwable) {
                    return $value->getMessage() . ' ' . $value->getTraceAsString();
                }
                if (method_exists($value, '__toString')) {
                    return $value->__toString();
                }
                break;
            case 'resource':
                return (string)$value;
            case 'resource (closed)':
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
}
