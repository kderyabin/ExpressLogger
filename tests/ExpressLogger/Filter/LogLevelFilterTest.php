<?php

declare(strict_types=1);

namespace ExpressLogger\Tests\Filter;

use ExpressLogger\Filter\LogLevelFilter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogLevelFilterTest extends TestCase
{
    /**
     * @testdox Must return false if log level is lower then allowed threshold.
     * @test
     */
    public function filterMinLevel()
    {
        $filter = new LogLevelFilter( LogLevel::INFO);
        $data = ['level' => LogLevel::DEBUG, 'message' => 'message'];
        $this->assertFalse($filter->filter($data));
    }
    /**
     * @testdox Must return false if log level is bigger then allowed threshold.
     * @test
     */
    public function filterMaxLevel()
    {
        $filter = new LogLevelFilter( null, LogLevel::INFO);
        $data = ['level' => LogLevel::WARNING, 'message' => 'message'];
        $this->assertFalse($filter->filter($data));
    }
    /**
     * @testdox Must return a log data if a log level is in between lower and upper threshold.
     * @test
     */
    public function filterMinMaxLevel()
    {
        $filter = new LogLevelFilter( LogLevel::INFO, LogLevel::WARNING);
        $data = ['level' => LogLevel::INFO, 'message' => 'message'];
        $this->assertIsArray($filter->filter($data));
    }
    /**
     * @testdox Must return a log data if the level is not declared in the filter.
     * @test
     */
    public function filterUnknownLevel()
    {
        $filter = new LogLevelFilter();
        $data = ['level' => 'devops', 'message' => 'message'];
        $this->assertIsArray($filter->filter($data));
    }
}
