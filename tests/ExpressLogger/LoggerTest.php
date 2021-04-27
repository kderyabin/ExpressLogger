<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
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
