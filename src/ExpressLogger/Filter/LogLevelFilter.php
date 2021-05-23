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
    protected array $levelCode = [
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
    public function __construct(?string $levelMin = null, ?string $levelMax = null)
    {
        if (null !== $levelMin) {
            $this->setLevelMin($this->levelCode[$levelMin] ??  PHP_INT_MIN);
        }
        if (null !== $levelMax) {
            $this->setLevelMax($this->levelCode[$levelMax] ??  PHP_INT_MAX);
        }
    }

    /**
     * @param array $data
     * @return array|false
     */
    public function filter(array $data)
    {
        $code = $this->levelCode[ $data['level'] ?? '' ] ?? $this->levelMin ;
        return $code >= $this->levelMin && $code <= $this->levelMax ? $data : false;
    }

    /**
     * @return array|int[]
     */
    public function getLevelCode(): array
    {
        return $this->levelCode;
    }

    /**
     * @param array|int[] $levelCode
     * @return LogLevelFilter
     */
    public function setLevelCode(array $levelCode): LogLevelFilter
    {
        $this->levelCode = $levelCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevelMin(): int
    {
        return $this->levelMin;
    }

    /**
     * @param int|mixed $levelMin
     * @return LogLevelFilter
     */
    public function setLevelMin($levelMin): LogLevelFilter
    {
        $this->levelMin = $levelMin;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevelMax(): int
    {
        return $this->levelMax;
    }

    /**
     * @param int|mixed $levelMax
     * @return LogLevelFilter
     */
    public function setLevelMax($levelMax): LogLevelFilter
    {
        $this->levelMax = $levelMax;
        return $this;
    }
}
