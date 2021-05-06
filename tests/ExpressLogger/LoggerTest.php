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
}
