<?php

/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\API\WriterInterface;
use ExpressLogger\ExpressLogger;
use ExpressLogger\LoggingStrategy\ExpressStrategy;
use ExpressLogger\LoggingStrategy\StandardStrategy;
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
        $logger = new ExpressLogger($writer, ['client_ip' => '127.0.0.1']);
        $logger->setLoggingStrategy(new StandardStrategy());
        $logger->log('debug', 'message', [ 'extra' => 'yes']);

        $this->assertArrayHasKey('datetime', $writer->log);
        $this->assertArrayHasKey('message', $writer->log);
        $this->assertArrayHasKey('level', $writer->log);
        $this->assertArrayHasKey('client_ip', $writer->log);
        $this->assertArrayHasKey('extra', $writer->log);
    }
    /**
     * @testdox Buffered logging
     */
    public function testExpressModeBufferedLogging()
    {
        $writer = new class () implements WriterInterface {
            public array $log = [];

            public function write(array $log): bool
            {
                return true;
            }

            public function process(array $logs): int
            {
                return count($this->log = $logs);
            }
        };
        $logger = new ExpressLogger($writer);
        $xStrategy = new ExpressStrategy(false, 0);
        $xStrategy->setMemoryLimit(0);
        $logger->setLoggingStrategy($xStrategy);
        $logger->log('debug', 'message');

        $this->assertNotEmpty($writer->log);
    }

    /**
     * @testdox Batch logging
     */
    public function testBact()
    {
        $writer = new class () implements WriterInterface {
            public array $log = [];

            public function write(array $log): bool
            {
                return true;
            }

            public function process(array $logs): int
            {
                return count($this->log = $logs);
            }
        };
        $logger = new ExpressLogger($writer);
        $logger->setExpressMode(true, false);

        $logger->log('debug', 'message 1');
        $logger->log('debug', 'message 2');

        $this->assertEmpty($writer->log);
        $logger->batch();
        $this->assertEquals(2, count($writer->log));
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

    /**
     * @testdox Express mode params initialized when express mode is enabled
     */
    public function testSetExpressModeOn()
    {
        ini_set('memory_limit', '10M');
        $logger = new ExpressLogger();
        $logger->setExpressMode(true, true, 10, 15);

        $this->assertEquals(10, $logger->getMemWatchThreshold());
        $this->assertEquals(15, $logger->getBufferSize());
        $this->assertTrue($logger->isUseFlush());
        $this->assertTrue($logger->isExpressMode());
        $this->assertNotEquals(-1, $logger->getMemoryLimit());
    }

    /**
     * @testdox Express mode params initialized when express mode is disabled
     */
    public function testSetExpressModeOff()
    {
        ini_set('memory_limit', '10M');
        $logger = new ExpressLogger();
        $logger->setExpressMode(false, true, 10, 15);

        $this->assertEquals(10, $logger->getMemWatchThreshold());
        $this->assertEquals(15, $logger->getBufferSize());
        $this->assertFalse($logger->isUseFlush());
        $this->assertFalse($logger->isExpressMode());
        $this->assertEquals(-1, $logger->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in megabytes to bytes & apply a ratio
     */
    public function testMemLimitM()
    {
        ini_set('memory_limit', '10M');
        $logger = new ExpressLogger();
        $logger->setExpressMode(true);

        $this->assertEquals(intval(10 * 0.6 * (1024 ** 2)), $logger->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in gigabytes to bytes & apply a ratio
     */
    public function testMemLimitG()
    {
        ini_set('memory_limit', '1G');
        $logger = new ExpressLogger();
        $logger->setExpressMode(true);
        $this->assertEquals(intval(1 * 0.6 * (1024 ** 3)), $logger->getMemoryLimit());
    }
    /**
     * @testdox Must convert memory_limit set in kilobytes to bytes & apply a ratio
     */
    public function testMemLimitK()
    {
        ini_set('memory_limit', '1K');
        $logger = new ExpressLogger();
        $logger->setExpressMode(true);

        $this->assertEquals(intval(1 * 0.6 * (1024)), $logger->getMemoryLimit());
    }
    /**
     * @testdox Must set -1 if no memory limit is set
     */
    public function testMemLimit()
    {
        ini_set('memory_limit', '-1');
        $logger = new ExpressLogger();
        $logger->setExpressMode(true);
        $this->assertEquals(-1, $logger->getMemoryLimit());
    }
}
