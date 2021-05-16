<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\LoggingStrategy;

use ExpressLogger\API\WriterInterface;
use ExpressLogger\LoggingStrategy\StandardStrategy;
use PHPUnit\Framework\TestCase;

class StandardStrategyTest extends TestCase
{
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

        $strategy = new StandardStrategy();
        $strategy->setWriters([$writer]);
        $strategy->process(['message' => 'ok']);

        $this->assertArrayHasKey('message', $writer->log);
    }

}
