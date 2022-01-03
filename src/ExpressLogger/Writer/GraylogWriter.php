<?php

declare(strict_types=1);

namespace ExpressLogger\Writer;

use ExpressLogger\API\FormatterInterface;
use ExpressLogger\Formatter\GraylogFormatter;

/**
 * Handler to send messages to a Graylog2
 */
class GraylogWriter extends SocketWriter
{
    public const CHUNK_GELF_ID = "\x1e\x0f";
    public const CHUNK_MAX_COUNT = 128; // as per GELF spec
    public const CHUNK_SIZE = 8192;
    public const CHUNK_HEADER_LENGTH = 12; // GELF ID (2b), id (8b) , sequence (2b)


    protected $isTCP = false;
    protected $chunkSize = self::CHUNK_SIZE;

    public function __construct(
        string $address,
        int $chunkSize = self::CHUNK_SIZE,
        ?array $context = null,
        ?FormatterInterface $formatter = null,
    ) {
        parent::__construct($address, $context, $formatter ?? new GraylogFormatter());
        $this->chunkSize = $chunkSize;

        $this->setIsTCP(str_starts_with($this->address, 'tcp'));
    }

    /**
     * Sends message in multiple chunks
     * @param string $message
     * @return bool
     */
    protected function writeInChunks(string $message): bool
    {
        // split to chunks
        $chunks = mb_str_split($message, $this->chunkSize - self::CHUNK_HEADER_LENGTH);
        $numChunks = count($chunks);

        if ($numChunks > self::CHUNK_MAX_COUNT) {
            return false;
        }

        // generate a random 8byte-message-id
        $messageId = substr(md5(uniqid("", true), true), 0, 8);

        foreach ($chunks as $idx => $chunk) {
            $data = self::CHUNK_GELF_ID            // GELF chunk magic bytes
                . $messageId                       // unique message id
                . pack('CC', $idx, $numChunks)     // sequence information
                . $chunk                           // chunk-data
            ;
            if (false === @fwrite($this->socket, $data)) {
                return false;
            }
        }

        return true;
    }

    public function write(array $log): bool
    {
        if ($this->isDisabled) {
            return false;
        }
        if ($this->isClosed() && !$this->open()) {
            return false;
        }

        $log = $this->applyFilters($log);
        if (!$log) {
            return false;
        }
        $message = $this->getFormatter()->format($log);
        if ($this->isTCP()) {
            $message .= "\0";
        }

        if ($this->isUDP() && mb_strlen($message) > $this->chunkSize) {
            return $this->writeInChunks($message);
        }

        return $this->writeToSocket($message);
    }

    protected function processTCP(array $logs): int
    {
        $count = 0;
        $message = '';
        foreach ($logs as $data) {
            $log = $this->applyFilters($data);
            if (!$log) {
                continue;
            }
            $message .= $this->getFormatter()->format($log) . "\0";
            $count++;
        }

        return $this->writeToSocket($message) ? $count : 0;
    }
    protected function processUDP(array $logs): int
    {
        $count = 0;
        foreach ($logs as $data) {
            $this->write($data) && ++$count;
        }

        return $count;
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
        if ($this->isClosed() && !$this->open()) {
            return 0;
        }
        if ($this->isUDP()) {
            return $this->processUDP($logs);
        }
        return $this->processTCP($logs);
    }
    /**
     * @return bool
     */
    public function isTCP(): bool
    {
        return $this->isTCP;
    }

    /**
     * @param bool $isTCP
     */
    public function setIsTCP(bool $isTCP): void
    {
        $this->isTCP = $isTCP;
    }
}
