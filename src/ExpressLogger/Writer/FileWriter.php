<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FilterCollectionInterface, FormatterInterface, WriterInterface};
use ExpressLogger\Filter\FilterCollectionTrait;
use ExpressLogger\Formatter\JsonFormatter;

/**
 * Class FileWriter
 * @package ExpressLogger\Writer
 */
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
     * @var FormatterInterface
     */
    protected FormatterInterface $formatter;
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
        $this->setPath($path);
        $this->setFormatter($formatter ?? new JsonFormatter());
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
        if (!$this->resource && !$this->open()) {
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
        if ($this->isDisabled) {
            return 0;
        }
        if (!$this->resource && !$this->open()) {
            return false;
        }
        $count = 0;
        $msg = '';
        foreach ($logs as $data) {
            $log = $this->applyFilters($data);
            if (!$log) {
                continue;
            }

            $msg .= $this->formatter->format($log);
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
        $this->resource = @fopen($this->path, 'ab');
        if (false === $this->resource) {
            $this->resource = null;
            $this->setIsDisabled(true);
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
            $this->resource = null;
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return FileWriter
     */
    public function setPath(string $path): FileWriter
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * @param FormatterInterface $formatter
     * @return FileWriter
     */
    public function setFormatter(FormatterInterface $formatter): FileWriter
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     * @return FileWriter
     */
    public function setIsDisabled(bool $isDisabled): FileWriter
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }
}
