<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FilterCollectionInterface, FormatterInterface, WriterInterface};
use ExpressLogger\Filter\FilterCollectionTrait;
use ExpressLogger\Formatter\JsonFormatter;

class FileWriter implements WriterInterface, FilterCollectionInterface
{
    use FilterCollectionTrait;

    /**
     * Log file path
     * @var string
     */
    protected string $path;
    /**
     * Resource where logs will be written.
     * @var resource
     */
    private $resource;
    /**
     * Formatter used for this handler
     * @var null|FormatterInterface
     */
    protected ?FormatterInterface $formatter;
    /**
     * Disables logging.
     * This option is set automatically to TRUE if a log file can't be opened.
     * @var bool
     */
    protected bool $isDisabled = false;

    /**
     * File constructor.
     * @param string $path
     * @param FormatterInterface|null $formatter
     */
    public function __construct(string $path, ?FormatterInterface $formatter = null)
    {
        $this->path = $path;
        $this->formatter = $formatter ?? new JsonFormatter();
        $this->open();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Write a log message.
     * @param array $log
     * @return bool
     */
    public function write(array $log): bool
    {
        if ($this->isDisabled) {
            return false;
        }

        $log = $this->applyFilters($log);

        return $log && (@fwrite($this->resource, $this->formatter->format($log)) !== false);
    }

    /**
     * @param array $logs
     * @return int
     */
    public function process(array $logs): int
    {
        if (empty($logs)) {
            return 0;
        }
        if ($this->isDisabled) {
            return 0;
        }

        $count = 0;
        $msg = '';
        foreach ($logs as $data) {
            $log = $this->applyFilters($data);
            if (false === $log) {
                continue;
            }

            $msg .= $this->formatter->format($this->applyFilters($data));
            ++$count;
        }
        @fwrite($this->resource, $msg);
        return $count;
    }


    /**
     * Open a log destination.
     * @return bool
     */
    public function open(): bool
    {
        $this->resource = fopen($this->path, 'ab');
        if (false === $this->resource) {
            $this->isDisabled = true;
            return false;
        }
        return true;
    }

    /**
     * Close a resource
     */
    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }
}
