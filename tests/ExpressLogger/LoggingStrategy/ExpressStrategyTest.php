<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\LoggingStrategy;

use ExpressLogger\API\WriterInterface;
use ExpressLogger\LoggingStrategy\ExpressStrategy;
use PHPUnit\Framework\TestCase;

class ExpressStrategyTest extends TestCase
{

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
        $strategy = new ExpressStrategy(false, 0);
        $strategy->setMemoryLimit(0);
        $strategy->setWriters([$writer]);

        $strategy->process(['level' => 'debug']);

        $this->assertNotEmpty($writer->log);
    }


    /**
     * @testdox Batch logging
     */
    public function testBatchLogging()
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

        $strategy = new ExpressStrategy(false);
        $strategy->setMemoryLimit(0);
        $strategy->setWriters([$writer]);

        $strategy->process(['level' => 'debug']);
        $strategy->process(['level' => 'debug']);


        $this->assertEmpty($writer->log);
        $strategy->batch();
        $this->assertEquals(2, count($writer->log));
    }

    /**
     * @testdox Express mode params initialized when express mode is enabled
     */
    public function testInitialization()
    {
        ini_set('memory_limit', '10M');
        $strategy = new ExpressStrategy(true, 10, 15);

        $this->assertEquals(10, $strategy->getMemWatchThreshold());
        $this->assertEquals(15, $strategy->getBufferSize());
        $this->assertTrue($strategy->isUseFlush());
        $this->assertNotEquals(-1, $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in megabytes to bytes & apply a ratio
     */
    public function testMemLimitM()
    {
        ini_set('memory_limit', '10M');
        $strategy = new ExpressStrategy();
        $this->assertEquals(intval(10 * 0.6 * (1024 ** 2)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in gigabytes to bytes & apply a ratio
     */
    public function testMemLimitG()
    {
        ini_set('memory_limit', '1G');
        $strategy = new ExpressStrategy();
        $this->assertEquals(intval(1 * 0.6 * (1024 ** 3)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in kilobytes to bytes & apply a ratio
     */
    public function testMemLimitK()
    {
        ini_set('memory_limit', '10000K');
        $strategy = new ExpressStrategy();
        $this->assertEquals(intval(10000 * 0.6 * (1024)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must set -1 if no memory limit is set
     */
    public function testMemLimit()
    {
        ini_set('memory_limit', '-1');
        $strategy = new ExpressStrategy();
        $this->assertEquals(-1, $strategy->getMemoryLimit());
    }
}
