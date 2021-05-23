<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\API\LoggingStrategyInterface;
use ExpressLogger\API\WriterInterface;
use ExpressLogger\ExpressLogger;
use ExpressLogger\LoggingStrategy\ExpressStrategy;
use PHPUnit\Framework\TestCase;

class ExpressLoggerTest extends TestCase
{
    /**
     * @testdox ExpressLogger initialization
     */
    public function testInitLogger()
    {
        $writer = new class () implements WriterInterface {
            public function write(array $log): bool
            {
                return true;
            }

            public function process(array $logs): int
            {
                return 0;
            }
        };
        $logger = new ExpressLogger($writer, ['client_ip' => '127.0.0.1']);
        $this->assertNotEmpty($logger->getWriters());
        $this->assertArrayHasKey('client_ip', $logger->getFields());
        $this->assertInstanceOf(ExpressStrategy::class ,$logger->getLoggingStrategy());
    }

    /**
     * @testdox Should initialize log data and pass it to the writer.
     */
    public function testLog()
    {
        $writer = new class () implements WriterInterface {
            public array $log = [];

            public function write(array $log): bool
            {
                $this->log = $log;
                return true;
            }

            public function process(array $logs): int
            {
                return 0;
            }
        };
        $strategy = new class() implements LoggingStrategyInterface {
            /**
             * @var WriterInterface[]
             */
            protected array $writers = [];

            /**
             * @param  WriterInterface[] $writers
             */
            public function setWriters(array $writers): void
            {
               $this->writers = $writers;
            }

            public function process(array $data): void
            {
                $this->writers[0]->write($data);
            }
        };
        $logger = new ExpressLogger($writer, ['client_ip' => '127.0.0.1']);
        $logger->setLoggingStrategy($strategy);
        $logger->log('debug', 'message', [ 'extra' => 'yes']);

        $this->assertArrayHasKey('datetime', $writer->log);
        $this->assertArrayHasKey('message', $writer->log);
        $this->assertArrayHasKey('level', $writer->log);
        $this->assertArrayHasKey('client_ip', $writer->log);
        $this->assertArrayHasKey('extra', $writer->log);
    }

    /**
     * @testdox Should set a field.
     * @test
     */
    public function setField()
    {
        $logger = new ExpressLogger();
        $logger->setField('client_ip', '127.0.0.1');
        $this->assertContains('127.0.0.1', $logger->getFields());
        $this->assertArrayHasKey('client_ip', $logger->getFields());
    }

    /**
     * @testdox Should set fields.
     * @test
     */
    public function setFields()
    {
        $logger = new ExpressLogger();
        $logger->setFields([
            'client_ip' => '127.0.0.1',
            'host' => 'localhost'
        ]);

        $this->assertArrayHasKey('host', $logger->getFields());
        $this->assertArrayHasKey('client_ip', $logger->getFields());
    }
}
