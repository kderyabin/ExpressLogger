<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Writer;

use ExpressLogger\Writer\ErrorLogWriter;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\TestCase;

class ErrorLogWriterTest extends TestCase
{
    protected static string $errorLogConfig;

    /**
     * @beforeClass
     */
    public static function start()
    {
        static::$errorLogConfig = ini_get('error_log');
        ini_set('error_log', TEST_LOG_FILE);
    }

    /**
     * @afterClass
     */
    public static function end()
    {
        ini_set('error_log', static::$errorLogConfig);
    }

    /**
     * @before
     */
    public function emptyLogFile()
    {
        if (is_file(TEST_LOG_FILE)) {
            unlink(TEST_LOG_FILE);
            touch(TEST_LOG_FILE);
        }
    }

    /**
     * @testdox Should append a log to the log file.
     * @test
     */
    public function write()
    {
        $writer = new ErrorLogWriter();
        $log = ['log_level' => 'debug', 'message' => 'ok'];
        $this->assertTrue($writer->write($log));
    }

    /**
     * @test
     */
    public function process()
    {
        $writer = new ErrorLogWriter();
        $log = ['message' => 'Lorem ipsum dolor sit amet.'];
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = $log;
        }
        $this->assertEquals(20, $writer->process($data));
    }
}
