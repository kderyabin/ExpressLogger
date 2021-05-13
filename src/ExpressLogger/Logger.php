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
    protected array $fields = [];
    /**
     * Application logs must be processed after application is run.
     * @var bool
     */
    protected bool $isExpressMode = true;
    /**
     * Flag saying if the content must flushed before logs are processed.
     * Can be activated only ifs Logger::isExpressMode is enabled.
     * @var bool
     */
    protected bool $useFlush = true;
    /**
     * Collection of logs.
     * Used in express mode.
     * @var array
     */
    protected array $queue = [];
    /**
     * Number of logs in the queue.
     * @var int
     */
    protected int $queueSize = 0;
    /**
     * The memory threshold in bytes after which the logger starts to write logs by batch of $bufferSize.
     * -1: memory limitation is disabled.
     * @var int
     */
    protected int $memoryLimit = -1;
    /**
     *  Number of logs to process when allowed memory threshold is achieved (60% of memory_limit setting).
     * @var int
     */
    protected int $bufferSize = 200;
    /**
     * In express mode this is a maximum number of logs in a queue after which the memory usage tracking is started.
     * @var int
     */
    protected int $memWatchThreshold = 1000;

    /**
     * @param WriterInterface|WriterInterface[] $writers One or an array of writers.
     * @param array $extraFields Additional log fields with constant values.
     *                              See Logger::setFields() ro reset default fields.
     * @param bool $isExpressMode Enable express mode.
     * @throws Exception Emits Exception in case of an error.
     */
    public function __construct($writers = [], array $extraFields = [], bool $isExpressMode = true)
    {
        $this->datetimeTracker = new DateTimeTracker();
        if ($writers) {
            if (!is_array($writers)) {
                $this->addWriter($writers);
            } else {
                foreach ($writers as $writer) {
                    $this->addWriter($writer);
                }
            }
        }
        if ($extraFields) {
            foreach ($extraFields as $field => $value) {
                $this->setField($field, $value);
            }
        }
        register_shutdown_function([$this, 'batch']);
        $this->setExpressMode($isExpressMode);
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
     * @return Logger
     */
    public function setField(string $name, $value): Logger
    {
        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * Configure express mode.
     * @param bool $isExpressMode Enable/disable express mode
     * @param bool $useFlush Enable/disable flush content.
     * @param int $memWatchThreshold Number of logs in a queue after which the memory usage tracking is started
     * @param int $bufferSize Number of logs to process when allowed memory size is achieved.
     */
    public function setExpressMode(
        bool $isExpressMode,
        bool $useFlush = true,
        int $memWatchThreshold = 1000,
        int $bufferSize = 200
    ): Logger {
        $this->isExpressMode = $isExpressMode;
        $this->bufferSize = $bufferSize;
        $this->memWatchThreshold = $memWatchThreshold;
        if ($isExpressMode) {
            $this->useFlush = $useFlush;
            $this->calcMemoryLimit();
        } else {
            $this->useFlush = false;
            $this->setMemoryLimit(-1);
        }

        return $this;
    }

    /**
     * Calculates a memory limit for express mode.
     * Threshold is a 60% of allowed memory if is it's set up. See 'memory_limit' setting in php.ini.
     * Once the threshold is achieved the logger starts to write logs by batch.
     */
    public function calcMemoryLimit(): void
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = intval($memoryLimit);

        if ($memoryLimitBytes == -1) {
            // No limit.
            $this->memoryLimit = -1;
            return;
        }
        if ($memoryLimit != (string)$memoryLimitBytes) {
            switch (strtoupper(substr($memoryLimit, -1))) {
                case 'G':
                    $memoryLimitBytes *= 1073741824;
                    break;
                case 'M':
                    $memoryLimitBytes *= 1048576;
                    break;
                case 'K':
                    $memoryLimitBytes *= 1024;
                    break;
            }
        }
        $this->memoryLimit = intval($memoryLimitBytes * 0.6);
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
            ++$this->queueSize;
            if (
                $this->queueSize > $this->memWatchThreshold
                && $this->memoryLimit !== -1
                && memory_get_usage(true) > $this->memoryLimit
            ) {
                $this->batchBuffer();
            }
            return;
        }

        foreach ($this->writers as $writer) {
            $writer->write($data);
        }
    }

    /**
     * Process a part of logs queue in express mode.
     */
    protected function batchBuffer(): void
    {
        if (!$this->queueSize) {
            return;
        }
        $data = array_splice($this->queue, 0, $this->bufferSize);
        foreach ($this->writers as $handler) {
            $handler->process($data);
        }
        $this->queueSize = count($this->queue);
    }

    /**
     * Process logs queue in express mode.
     */
    public function batch(): void
    {
        if (!$this->isExpressMode) {
            return;
        }

        if (empty($this->queue)) {
            return;
        }
        $this->flush();
        foreach ($this->writers as $writer) {
            $writer->process($this->queue);
        }
        $this->queueSize = count($this->queue);
    }

    /**
     * Flush content before logging in express mode.
     */
    public function flush(): void
    {
        if (!$this->useFlush) {
            return;
        }
        if (function_exists('fastcgi_finish_request')) {
            // web server & php-fpm env
            fastcgi_finish_request();
        } else {
            // cli
            @ob_flush();
            @flush();
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
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return Logger
     */
    public function setFields(array $fields): Logger
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return int
     */
    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }

    /**
     * @param int $memoryLimit -1 to disable memory limit or memory size in bytes.
     */
    public function setMemoryLimit(int $memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @return bool
     */
    public function isExpressMode(): bool
    {
        return $this->isExpressMode;
    }

    /**
     * @return bool
     */
    public function isUseFlush(): bool
    {
        return $this->useFlush;
    }

    /**
     * @return int
     */
    public function getBufferSize(): int
    {
        return $this->bufferSize;
    }


    /**
     * @return int
     */
    public function getMemWatchThreshold(): int
    {
        return $this->memWatchThreshold;
    }
}
