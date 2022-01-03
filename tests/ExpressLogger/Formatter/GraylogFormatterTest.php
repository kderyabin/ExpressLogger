<?php

namespace ExpressLogger\Tests\Formatter;

use ExpressLogger\Formatter\GraylogFormatter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class GraylogFormatterTest extends TestCase
{

    public function testFormat()
    {
        $formatter = new GraylogFormatter();

        $log = [
            'datetime' => new \DateTime('2021-05-01 03:00:00.123'),
            'message' => 'Log message',
            'level' => LogLevel::WARNING,
            'host' => 'express',
            'id' => 1000,
            'file' => 'someFile.php',
            'line' => 20,
        ];

        $message = $formatter->format($log);
        $result = json_decode($message, true);

        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('full_message', $result);
        $this->assertArrayHasKey('short_message', $result);
        $this->assertArrayHasKey('host', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('_x_id', $result);
        $this->assertArrayHasKey('_file', $result);
        $this->assertArrayHasKey('_line', $result);
    }
}
