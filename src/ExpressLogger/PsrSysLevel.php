<?php

declare(strict_types=1);

namespace ExpressLogger;

use Psr\Log\LogLevel;

class PsrSysLevel
{
    /**
     * Map Psr logger levels to system levels
     * @var array
     */
    public static array $psrToSysLevel = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    public static function getSysLevel(string $level): int
    {
        return self::$psrToSysLevel[$level] ?? LOG_INFO;
    }
}
