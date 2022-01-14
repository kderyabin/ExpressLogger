<?php

declare(strict_types=1);

namespace ExpressLogger\Writer;

use ExpressLogger\API\FilterCollectionInterface;
use ExpressLogger\API\FormatterInterface;
use ExpressLogger\API\WriterInterface;
use ExpressLogger\Filter\FilterCollectionTrait;
use ExpressLogger\Formatter\JsonFormatter;

class SocketWriter implements WriterInterface, FilterCollectionInterface
{
    use FilterCollectionTrait;

    /**
     * Formatter used for this handler
     * @var FormatterInterface
     */
    protected FormatterInterface $formatter;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * Disables logging.
     * This option is set automatically to TRUE if a log file can't be opened.
     * @var bool
     */
    protected bool $isDisabled = false;

    protected $isUDP = false;

    /**
     * @param string $address Address to connect to. Must include the transport, ex. tcp://www.example.com:80
     * @param array|null $context Args for the stream_context_create() function.
     * @param FormatterInterface|null $formatter
     */
    public function __construct(
        protected string $address,
        protected ?array $context = null,
        ?FormatterInterface $formatter = null
    ) {
        $this->setFormatter($formatter ?? new JsonFormatter());
        $this->setIsUDP(str_starts_with($this->address, 'udp'));
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes underlying socket
     */
    public function close(): void
    {
        if (!is_resource($this->socket)) {
            return;
        }

        fclose($this->socket);
        $this->socket = null;
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
        return $this->writeToSocket($this->getFormatter()->format($log));
    }

    /**
     * Checks if the socket is closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->socket === null;
    }

    /**
     * @return bool
     */
    public function open(): bool
    {
        $this->socket = @stream_socket_client(
            $this->address,
            $errNo,
            $errStr,
            null,
            STREAM_CLIENT_ASYNC_CONNECT,
            $this->context ? stream_context_create($this->context) : null
        );

        if (!$this->socket) {
            $this->isDisabled = true;

            return false;
        }

        // set non-blocking for UDP
        if ($this->isUDP()) {
            stream_set_blocking($this->socket, false);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUDP(): bool
    {
        return $this->isUDP;
    }

    /**
     * @param bool $isUDP
     */
    public function setIsUDP(bool $isUDP): void
    {
        $this->isUDP = $isUDP;
    }

    /**
     * Write to socket.
     * @param string $message
     * @return bool TRUE on success FALSE on failure.
     */
    protected function writeToSocket(string $message): bool
    {
        $strlen = mb_strlen($message, '8bit');
        $written = 0;
        while ($written < $strlen) {
            $byteCount = @fwrite($this->socket, mb_substr($message, $written));
            if ($byteCount === false) {
                return false;
            }
            $written += $byteCount;
        }
        return true;
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
     */
    public function setFormatter(FormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

    public function process(array $logs): int
    {
        if ($this->isDisabled) {
            return 0;
        }
        if ($this->isClosed() && !$this->open()) {
            return 0;
        }
        $count = 0;
        foreach ($logs as $data) {
            $log = $this->applyFilters($data);
            if (!$log) {
                continue;
            }
            $this->writeToSocket($this->getFormatter()->format($log)) && ++$count;
        }
        return $count;
    }

    /**
     * Returns the stream context
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the stream context
     *
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }
}
