<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FormatterInterface, WriterInterface};
use Psr\Log\LogLevel;

/**
 * Class SyslogWriter
 * @package ExpressLogger\Handlers
 */
class SyslogWriter implements WriterInterface
{
    use LogLevelTrait;

    /**
     * Formatter used for this handler
     * @var null|FormatterInterface
     */
    protected ?FormatterInterface $formatter;

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
     * Map Psr logger levels to system levels
     * @var array
     */
    protected array $sysLevel = [
        LogLevel::EMERGENCY => LOG_EMERG,
        LogLevel::ALERT => LOG_ALERT,
        LogLevel::CRITICAL => LOG_CRIT,
        LogLevel::ERROR => LOG_ERR,
        LogLevel::WARNING => LOG_WARNING,
        LogLevel::NOTICE => LOG_NOTICE,
        LogLevel::INFO => LOG_INFO,
        LogLevel::DEBUG => LOG_DEBUG,
    ];
    /**
     * Disables logging.
     * This option is set automatically to TRUE if log destination can't be opened.
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
        if ($formatter) {
            $this->formatter = $formatter;
        }
        if ($prefix) {
            $this->prefix = $prefix;
        }
        if ($flags) {
            $this->flags = $flags;
        }
        if ($facility) {
            $this->facility = $facility;
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
            $this->isDisabled = true;
            return false;
        }
        return true;
    }

    /**
     * @param string $level
     * @return int
     */
    public function getSystemLevel(string $level): int
    {
        return $this->sysLevel[$level] ?? LOG_INFO;
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

        if (!$this->canLog($log['level_code'] ?? $this->codeLevelMin)) {
            return false;
        }
        return syslog($this->getSystemLevel($log['level']), $this->formatter->format($log));
    }


    public function process(array $logs): int
    {
        $count = 0;
        if ($this->isDisabled) {
            return $count;
        }
        foreach ($logs as $log) {
            if (!$this->canLog($log['level_code'] ?? $this->codeLevelMin)) {
                continue;
            }
            syslog($this->getSystemLevel($log['level']), $this->formatter->format($log));
            $count++;
        }
        return $count;
    }
}
