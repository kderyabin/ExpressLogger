<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\Formatter\JsonFormatter;
use ExpressLogger\Formatter\JsonPrettyFormatter;
use ExpressLogger\Formatter\LinePatternFormatter;
use ExpressLogger\Logger;
use ExpressLogger\Writer\FileWriter;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    /**
     * @testdox Should create logger.
     * @test
     */
    public function creation()
    {
        $this->markTestSkipped('@to implement');
        $logger = new Logger();
//        $logger->addWriter( new FileWriter(TEST_LOG_FILE, new LinePatternFormatter()));
        $logger->addWriter( new FileWriter(TEST_LOG_FILE, new JsonFormatter(null, PHP_EOL)));
        $logger->setIsTurbo(false);
        $eta = -hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $logger->info('Hello', ['exception' => 'The argument is evil']);
//
//        if($i === 9) {
//            throw new Exception('private exception');
//        }
        }
        $stop = $eta + hrtime(true);
        //$handler->batch();
        //$eta += hrtime(true);
        print_r([$stop / 1e+6]); //nanoseconds to milliseconds
    }
}
