<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Handlers;

use ExpressLogger\Formatter\KeyFormatter;
use ExpressLogger\Writer\SyslogWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class SyslogWriterTest extends TestCase
{
    /**
     * @testdox Should instantiate SyslogWriter and write a system log
     * @test
     */
    public function systemLogWriting()
    {
        $writer = new SyslogWriter( new KeyFormatter(null, ''), 'syslogWriter');
        $log = [ 'level' => LogLevel::INFO, 'message' => 'Lorem ipsum dolor sit amet.'];
        $this->assertTrue($writer->write($log));
    }

    /**
     * @test
     */
    public function Process()
    {
        $writer = new SyslogWriter( new KeyFormatter(null, ''), 'syslogWriter');
        $data = [];
        $count = 10;
        for ($i = 0; $i < $count; $i++) {
            $data[] =  [
                'level' => LogLevel::INFO,
                'message' =>  microtime(true)
            ];
            usleep(100);
        }
        $this->assertEquals($count, $writer->process($data));
    }
}
