<?php
/**
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\API\WriterInterface;
use ExpressLogger\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    /**
     * @test
     */
    public function initLogger()
    {
        $writer = new class() implements WriterInterface {
            public function write(array $log): bool
            {
                return true;
            }

            public function process(array $logs): int
            {
                return 0;
            }
        };
        $logger = new Logger($writer, ['client_ip' => '127.0.0.1']);
        $this->assertNotEmpty($logger->getWriters());
        $this->assertArrayHasKey('client_ip', $logger->getFields());
        $this->assertTrue($logger->isExpressMode());
        $this->assertTrue($logger->isUseFlush());
    }

    /**
     * @testdox Should set a field.
     * @test
     */
    public function setField()
    {
        $logger = new Logger();
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
        $logger = new Logger();
        $logger->setFields([
            'client_ip' => '127.0.0.1',
            'host' => 'localhost'
        ]);

        $this->assertArrayHasKey('host', $logger->getFields());
        $this->assertArrayHasKey('client_ip', $logger->getFields());
    }
    /**
     * @testdox Must convert memory_limit set in megabytes to bytes & apply a ratio
     */
    public function testMemLimitM()
    {
        ini_set('memory_limit', '10M');
        $logger = new Logger();

        $this->assertEquals( intval(10 * 0.6 * (1024**2)), $logger->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in gigabytes to bytes & apply a ratio
     */
    public function testMemLimitG()
    {
        ini_set('memory_limit', '1G');
        $logger = new Logger();

        $this->assertEquals( intval(1 * 0.6 * (1024**3)), $logger->getMemoryLimit());
    }
    /**
     * @testdox Must convert memory_limit set in kilobytes to bytes & apply a ratio
     */
    public function testMemLimitK()
    {
        ini_set('memory_limit', '1K');
        $logger = new Logger();

        $this->assertEquals( intval(1 * 0.6 * (1024)), $logger->getMemoryLimit());
    }
    /**
     * @testdox Must set -1 if no memory limit is set
     */
    public function testMemLimit()
    {
        ini_set('memory_limit', '-1');
        $logger = new Logger();

        $this->assertEquals(-1, $logger->getMemoryLimit());
    }
}
