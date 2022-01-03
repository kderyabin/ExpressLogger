<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Writer;

use ExpressLogger\Formatter\JsonFormatter;
use ExpressLogger\API\{FormatterInterface, WriterInterface};
use ExpressLogger\Filter\FilterCollectionTrait;
use ExpressLogger\PsrSysLevel;

/**
 * Class SyslogWriter
 * @package ExpressLogger\Writer
 */
class SyslogWriter implements WriterInterface
{
    use FilterCollectionTrait;

    /**
     * Formatter used for this handler
     * @var FormatterInterface
     */
    protected FormatterInterface $formatter;

    /**
     * The string prefix is added to each message.
     * @see https://www.php.net/manual/en/function.openlog.php
     * @var string
     */
    protected string $prefix = '';
    /**
     * The flags argument is used to indicate what logging options will be used when generating a log message.
     * @see https://www.php.net/manual/en/function.openlog.php
     * @var int
     */
    protected int $flags = LOG_ODELAY | LOG_PID | LOG_CONS;
    /**
     * Type of program is logging the message
     * @see https://www.php.net/manual/en/function.openlog.php
     * @var int
     */
    protected int $facility = LOG_USER;

    /**
     * Disables logging.
     * This option is set automatically to TRUE if a log destination can't be opened.
     * @var bool
     */
    protected bool $isDisabled = false;

    /**
     * SyslogWriter constructor.
     * @param FormatterInterface|null $formatter
     * @param string|null $prefix
     * @param int|null $flags
     * @param int|null $facility
     */
    public function __construct(
        ?FormatterInterface $formatter = null,
        ?string $prefix = null,
        ?int $flags = null,
        ?int $facility = null
    ) {
        $this->setFormatter($formatter ?? new JsonFormatter());
        if ($prefix) {
            $this->setPrefix($prefix);
        }
        if ($flags) {
            $this->setFlags($flags);
        }
        if ($facility) {
            $this->setFacility($facility);
        }
        $this->open();
    }

    /**
     * Open a connection to a system logger.
     *
     * @return bool
     */
    public function open(): bool
    {
        if (!openlog($this->prefix, $this->flags, $this->facility)) {
            $this->setIsDisabled(true);
            return false;
        }
        return true;
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

        return $log && syslog(PsrSysLevel::getSysLevel($log['level']), $this->formatter->format($log));
    }


    public function process(array $logs): int
    {
        $count = 0;
        if ($this->isDisabled) {
            return $count;
        }
        foreach ($logs as $log) {
            $log = $this->applyFilters($log);
            $log && syslog(PsrSysLevel::getSysLevel($log['level']), $this->formatter->format($log)) &&  ++$count;
        }
        return $count;
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
     * @return SyslogWriter
     */
    public function setFormatter(FormatterInterface $formatter): SyslogWriter
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return SyslogWriter
     */
    public function setPrefix(string $prefix): SyslogWriter
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     * @return SyslogWriter
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @return int
     */
    public function getFacility(): int
    {
        return $this->facility;
    }

    /**
     * @param int $facility
     * @return SyslogWriter
     */
    public function setFacility(int $facility): SyslogWriter
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return array
     */
    public function getSysLevel(): array
    {
        return $this->sysLevel;
    }

    /**
     * @param array $sysLevel
     * @return SyslogWriter
     */
    public function setSysLevel(array $sysLevel): SyslogWriter
    {
        $this->sysLevel = $sysLevel;
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
     * @return SyslogWriter
     */
    public function setIsDisabled(bool $isDisabled): SyslogWriter
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }
}
