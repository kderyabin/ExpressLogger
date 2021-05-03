<?php

declare(strict_types=1);

namespace ExpressLogger\Filter;

use ExpressLogger\API\FilterInterface;
use Psr\Log\LogLevel;

/**
 * Class LogLevelFilter
 * @package ExpressLogger\Filter
 */
class LogLevelFilter implements FilterInterface
{
    /**
     * @var array|int[]
     */
    public static array $levelCode = [
        LogLevel::EMERGENCY => 70,
        LogLevel::ALERT => 60,
        LogLevel::CRITICAL => 50,
        LogLevel::ERROR => 40,
        LogLevel::WARNING => 30,
        LogLevel::NOTICE => 20,
        LogLevel::INFO => 10,
        LogLevel::DEBUG => 0,
    ];

    protected int $levelMin = PHP_INT_MIN;
    protected int $levelMax = PHP_INT_MAX;

    /**
     * LogLevelFilter constructor.
     * @param string|null $levelMin
     * @param string|null $levelMax
     */
    public function __construct( ?string $levelMin = null , ?string $levelMax = null )
    {
        if(null !== $levelMin) {
            $this->levelMin = static::$levelCode[$levelMin] ??  PHP_INT_MIN;
        }
        if( null !== $levelMax) {
            $this->levelMax = static::$levelCode[$levelMax] ??  PHP_INT_MAX;
        }
    }

    /**
     *
     * @param array $data
     * @return array|false
     */
    public function filter(array $data)
    {
        $code = static::$levelCode[ $data['level'] ?? '' ] ?? $this->levelMin ;
        return $code >= $this->levelMin && $code <= $this->levelMax ? $data : false;
    }
}
