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
use DateTime;
use DateTimeZone;
use Exception;
use ExpressLogger\API\WriterInterface;
use Psr\Log\AbstractLogger;

/**
 * Class Logger
 * @package Logger
 */
class Logger extends AbstractLogger
{
    /**
     * @var WriterInterface[]
     */
    private array $writers = [];

    /**
     * @var int|float
     */
    private $timer;
    private DateInterval $dateInterval;
    private DateTime $dateTime;

    /**
     * Extra field to be injected into the log message.
     * @var array
     */
    protected array $fields = [
        'request_id' => ''
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
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateTime = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
        $this->timer = hrtime(true);
        $this->dateInterval = new DateInterval('PT0S');

        if ($this->isTurbo) {
            register_shutdown_function([$this, 'batch']);
        }
        $this->setField('request_id', uniqid());
    }


    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        // convert nanoseconds to microseconds for setting in DateInterval
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
        $data = [ 'datetime' => $this->getDate(), 'message' => $message, 'level' => $level, ] + array_merge($this->fields, $context);

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


    /**
     * @param string $name
     * @param mixed $value
     */
    public function setField(string $name, $value): void
    {
        $this->fields[$name] = $value;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }
}
