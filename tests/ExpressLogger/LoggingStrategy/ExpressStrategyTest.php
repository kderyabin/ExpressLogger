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
        $strategy = new class (true, 10, 15) extends ExpressStrategy {
            protected function getSystemMemoryLimit(): string|false
            {
                return '10m';
            }
        };
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
        $strategy = new class () extends ExpressStrategy {
            protected function getSystemMemoryLimit(): string|false
            {
                return '100m';
            }
        };

        $this->assertEquals(intval(100 * 0.6 * (1024 ** 2)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in gigabytes to bytes & apply a ratio
     */
    public function testMemLimitG()
    {
        $strategy = new class () extends ExpressStrategy {
            protected function getSystemMemoryLimit(): string|false
            {
                return '1G';
            }
        };

        $this->assertEquals(intval(1 * 0.6 * (1024 ** 3)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must convert memory_limit set in kilobytes to bytes & apply a ratio
     */
    public function testMemLimitK()
    {
        $strategy = new class () extends ExpressStrategy {
            protected function getSystemMemoryLimit(): string|false
            {
                return '1000K';
            }
        };

        $this->assertEquals(intval(1000 * 0.6 * (1024)), $strategy->getMemoryLimit());
    }

    /**
     * @testdox Must set -1 if no memory limit is set
     */
    public function testMemLimit()
    {

        $strategy = new class () extends ExpressStrategy {
            protected function getSystemMemoryLimit(): string|false
            {
                return '-1';
            }
        };

        $this->assertEquals(-1, $strategy->getMemoryLimit());
    }
}
