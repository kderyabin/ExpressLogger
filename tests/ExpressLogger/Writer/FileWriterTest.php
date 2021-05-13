<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Writer;

use ExpressLogger\API\FormatterInterface;
use ExpressLogger\Writer\FileWriter;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
    protected FormatterInterface $formatter;
    protected array $log = ['message' => 'ok'];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->formatter = new class () implements FormatterInterface {
            public function format(array $data): string
            {
                return json_encode($data);
            }
        };
        parent::__construct($name, $data, $dataName);
    }

    /** @test */
    public function write()
    {
        $writer = new FileWriter('php://output', $this->formatter);
        ob_start();
        $this->assertTrue($writer->write($this->log));
        $output = ob_get_clean();

        $this->assertStringContainsString('message', $output);
        $this->assertStringContainsString('ok', $output);
    }
    /** @test */
    public function writeDisabled()
    {
        $writer = new FileWriter('php://xxx', $this->formatter);
        $this->assertFalse($writer->write($this->log));
    }
    /** @test */
    public function writeEmptyFilter()
    {
        $writer = new FileWriter('php://output', $this->formatter);
        $writer->addFilter(fn($data) => []);
        $this->assertFalse($writer->write($this->log));
    }

    /** @test */
    public function process()
    {
        $data = [$this->log, $this->log];
        $writer = new FileWriter('php://output', $this->formatter);
        ob_start();
        $this->assertEquals(2, $writer->process($data));
        ob_end_clean();
    }
    /** @test */
    public function processDisabled()
    {
        $data = [$this->log];
        $writer = new FileWriter('php://xxx', $this->formatter);
        $this->assertEquals(0, $writer->process($data));
    }
    /** @test */
    public function processEmptyFilter()
    {
        $data = [$this->log];
        $writer = new FileWriter('php://output', $this->formatter);
        $writer->addFilter(fn($data) => []);
        $this->assertEquals(0, $writer->process($data));
    }
}
