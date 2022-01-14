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
use ExpressLogger\API\LoggingStrategyInterface;
use ExpressLogger\API\WriterInterface;
use ExpressLogger\LoggingStrategy\ExpressStrategy;
use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Class ExpressLogger
 * @package ExpressLogger
 */
class ExpressLogger extends AbstractLogger
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

    protected LoggingStrategyInterface $loggingStrategy;

    /**
     * @param WriterInterface|WriterInterface[] $writers One or an array of writers.
     * @param array $extraFields Additional log fields with constant values.
     *                              See ExpressLogger::setFields() ro reset default fields.
     * @throws Exception Emits Exception in case of an error.
     */
    public function __construct($writers = [], array $extraFields = [])
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
        $this->setLoggingStrategy(new ExpressStrategy());
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string | Stringable $message, array $context = []): void
    {
        $data = [
                'datetime' => $this->datetimeTracker->getNow(),
                'message' => (string) $message,
                'level' => $level,
            ] + array_merge($this->fields, $context);
        $this->loggingStrategy->process($data);
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
     * @return ExpressLogger
     */
    public function setField(string $name, $value): ExpressLogger
    {
        $this->fields[$name] = $value;
        return $this;
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
     * @return ExpressLogger
     */
    public function setFields(array $fields): ExpressLogger
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return LoggingStrategyInterface
     */
    public function getLoggingStrategy(): LoggingStrategyInterface
    {
        return $this->loggingStrategy;
    }

    /**
     * @param LoggingStrategyInterface $loggingStrategy
     */
    public function setLoggingStrategy(LoggingStrategyInterface $loggingStrategy): void
    {
        $this->loggingStrategy = $loggingStrategy;
        $this->loggingStrategy->setWriters($this->writers);
    }
}
