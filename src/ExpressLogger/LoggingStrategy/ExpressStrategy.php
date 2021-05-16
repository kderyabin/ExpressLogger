<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\LoggingStrategy;

use ExpressLogger\API\WriterInterface;

class ExpressStrategy implements LoggingStrategyInterface
{
    /**
     * @var WriterInterface[]
     */
    protected array $writers = [];

    /**
     * Flag saying if the content must flushed before logs are processed.
     * Can be activated only ifs ExpressLogger::isExpressMode is enabled.
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
     * Configure express mode.
     * @param bool $useFlush Enable/disable flush content.
     * @param int $memWatchThreshold Number of logs in a queue after which the memory usage tracking is started
     * @param int $bufferSize Number of logs to process when allowed memory size is achieved.
     */
    public function __construct(bool $useFlush = true, int $memWatchThreshold = 1000, int $bufferSize = 200)
    {
        $this->bufferSize = $bufferSize;
        $this->memWatchThreshold = $memWatchThreshold;
        $this->useFlush = $useFlush;
        $this->calcMemoryLimit();
        register_shutdown_function([$this, 'batch']);
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
     * @param WriterInterface[] $writers
     */
    public function setWriters(array $writers): void
    {
        $this->writers = $writers;
    }

    public function process(array $data): void
    {
        $this->queue[] = $data;
        ++$this->queueSize;
        if (
            $this->queueSize > $this->memWatchThreshold
            && $this->memoryLimit !== -1
            && memory_get_usage(true) > $this->memoryLimit
        ) {
            $this->batchBuffer();
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
     * Process logs queue in express mode.
     */
    public function batch(): void
    {
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
