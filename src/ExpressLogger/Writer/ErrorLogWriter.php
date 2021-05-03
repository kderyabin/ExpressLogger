<?php

/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger\Writer;

use ExpressLogger\API\{FilterCollectionInterface, FormatterInterface, WriterInterface};
use ExpressLogger\Filter\FilterCollectionTrait;
use ExpressLogger\Formatter\JsonFormatter;

/**
 * Class ErrorLogWriter is a wrapper for error_log() function.
 * Sends a message to the web server's error log or to a file according to php settings.
 *
 * @package Logger\Handlers
 * @see http://php.net/manual/function.error-log.php
 * @see http://php.net/manual/errorfunc.configuration.php#ini.error-log
 */
class ErrorLogWriter implements WriterInterface, FilterCollectionInterface
{
    use FilterCollectionTrait;

    /**
     * Formatter used for this handler
     * @var null|FormatterInterface
     */
    protected ?FormatterInterface $formatter;

    public function __construct(?FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter ?? new JsonFormatter();
    }

    /**
     * @param array $log
     * @return bool
     */
    public function write(array $log): bool
    {
        $log = $this->applyFilters($log);
        if (false === $log) {
            return false;
        }

        return error_log($this->formatter->format($log));
    }

    public function process(array $logs): int
    {
        $count = 0;
        $sizeLimit = ini_get('log_errors_max_len');
        ini_set('log_errors_max_len', '0');
        foreach ($logs as $log) {
            $log = $this->applyFilters($log);
            if (false === $log) {
                continue;
            }
            error_log($this->formatter->format($log));
            $count++;
        }
        ini_set('log_errors_max_len', $sizeLimit);
        return $count;
    }
}
