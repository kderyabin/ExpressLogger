<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger;

use DateInterval;
use DateTimeInterface;
use ExpressLogger\API\WriterInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class Logger
 * @package ExpressLogger
 */
class Logger extends AbstractLogger
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
    /**
     * @var WriterInterface[]
     */
    private array $writers = [];

    /**
     * @var int|float
     */
    private $timer;
    private DateInterval $dateInterval;
    private DateTimeInterface $dateTime;

    /**
     * Extra field to be injected into the log message.
     * @var array
     */
    protected array $fields = [
        'requestId' => 0
    ];
    /**
     * Flag for turbo mode.
     * @var bool
     */
    protected bool $isTurbo = true;
    /**
     * Logs used in turbo mode.
     * @var array
     */
    protected array $queue = [];

    /**
     * Logger constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateTime = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        $this->timer = hrtime(true);
        $this->dateInterval = new \DateInterval('PT0S');
        $this->fields['requestId'] = uniqid();
        if ($this->isTurbo) {
            register_shutdown_function([$this, 'batch']);
        }
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        $this->dateInterval->f = (hrtime(true) - $this->timer) / 1e+9;
        $this->dateTime->add($this->dateInterval);
        $this->timer = hrtime(true);

        return (clone $this->dateTime);
    }

    /**
     * @param bool $isTurbo
     */
    public function setIsTurbo(bool $isTurbo): void
    {
        $this->isTurbo = $isTurbo;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $data = [
                'datetime' => $this->getDate(),
                'message' => $message,
                'level' => $level,
                'level_code' => static::$levelCode[$level],
            ] + array_merge($this->fields, $context);

        if ($this->isTurbo) {
            $this->queue[] = $data;
            return;
        }

        foreach ($this->writers as $handler) {
            $handler->write($data);
        }
    }

    public function batch()
    {
        foreach ($this->writers as $handler) {
            $handler->process($this->queue);
        }
    }

    /**
     * Adds handler to the current channel.
     * @param WriterInterface $writer
     */
    public function addWriter(WriterInterface $writer)
    {
        $this->writers[] = $writer;
    }
}
