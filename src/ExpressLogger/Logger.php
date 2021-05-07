<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger;

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
    protected array $writers = [];

    protected DateTimeTracker $datetimeTracker;

    /**
     * Extra fields with constant values to be injected into the log message.
     * @var array
     */
    protected array $fields = [
        'request_id' => ''
    ];
    /**
     * Application logs must be processed after application is run.
     * @var bool
     */
    protected bool $isExpressMode = true;
    /**
     * Flashes the content before logs are processed.
     * Can be activated only ifs Logger::isExpressMode is enabled.
     * @var bool
     */
    protected bool $useFlush = false;
    /**
     * Logs used in turbo mode.
     * @var array
     */
    protected array $queue = [];

    /**
     * @param WriterInterface|WriterInterface[] $writers One or an array of writers.
     * @param array $fields Additional log fields with constant values. See Logger::setFields() ro reset default fields.
     * @throws Exception
     */
    public function __construct($writers = [], array $fields = [])
    {
        $this->datetimeTracker = new DateTimeTracker();
        if ($this->isExpressMode) {
            register_shutdown_function([$this, 'batch']);
        }
        $this->setField('request_id', uniqid());
        if ($writers) {
            if (!is_array($writers)) {
                $this->addWriter($writers);
            } else {
                foreach ($writers as $writer) {
                    $this->addWriter($writer);
                }
            }
        }
        if ($fields) {
            foreach ($fields as $field => $value) {
                $this->setField($field, $value);
            }
        }
    }

    /**
     * @param bool $isExpressMode
     * @param bool $useFlush
     */
    public function setExpressMode(bool $isExpressMode, bool $useFlush = false): void
    {
        $this->isExpressMode = $isExpressMode;
        $this->useFlush = $useFlush;
    }

    public function flushContent()
    {
        // @todo
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
                'datetime' => $this->datetimeTracker->getNow(),
                'message' => $message,
                'level' => $level,
            ] + array_merge($this->fields, $context);

        if ($this->isExpressMode) {
            $this->queue[] = $data;
            return;
        }

        foreach ($this->writers as $handler) {
            $handler->write($data);
        }
    }

    /**
     * Process logs queue in express mode.
     */
    public function batch(): void
    {
        foreach ($this->writers as $handler) {
            $handler->process($this->queue);
        }
    }

    /**
     * @return WriterInterface[]
     */
    public function getWriters(): array
    {
        return $this->writers;
    }

    /**
     * @param WriterInterface[] $writers
     */
    public function setWriters(array $writers): void
    {
        $this->writers = $writers;
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
