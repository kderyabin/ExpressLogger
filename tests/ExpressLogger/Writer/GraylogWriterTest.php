<?php

namespace ExpressLogger\Tests\Writer;

use ExpressLogger\Tests\Mock\SocketServer;
use ExpressLogger\Writer\GraylogWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class GraylogWriterTest extends TestCase
{
    /**
     * @testdox  Write a message (TCP)
     * @test
     */
    public function writeTCP()
    {
        $address = 'tcp://localhost:6789';
        $shm_key = ftok(__FILE__, 'a');
        $shmop = shmop_open($shm_key, "c", 0644, 1024);
        $data = [
            'datetime' => new \DateTime('2021-10-01 06:30:00.555'),
            'message' => 'Test message',
            'level' => LogLevel::CRITICAL,
        ];

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerTCP($address, $shmop);
            exit();
        }
        $pid = pcntl_fork();
        if ($pid === 0) {
            $writer = new GraylogWriter($address);
            $writer->write($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 1024));
        shmop_delete($shmop);

        $this->assertStringContainsString('Test message', $logged);
    }

    /**
     * @testdox  Write messages (TCP)
     * @test
     */
    public function processTCP()
    {
        $logs = [
            [
                'datetime' => new \DateTime('now'),
                'message' => '1st message',
                'level' => LogLevel::INFO,
            ],
            [
                'datetime' => new \DateTime('now'),
                'message' => '2nd message',
                'level' => LogLevel::ALERT,
            ],
        ];
        $address = 'tcp://localhost:6790';
        $shm_key = ftok(__FILE__, 'b');
        $shmop = shmop_open($shm_key, "c", 0644, 1024);

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerTCP($address, $shmop);
            exit();
        }
        $pid = pcntl_fork();
        if ($pid === 0) {
            $writer = new GraylogWriter($address);
            $writer->process($logs);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 1024));
        shmop_delete($shmop);

        $this->assertStringContainsString("\0", $logged);
        $this->assertStringContainsString('1st message', $logged);
        $this->assertStringContainsString('2nd message', $logged);
    }

    /**
     * @testdox Write 1 message (UDP)
     * @test
     */
    public function writeUDP()
    {
        $address = 'udp://localhost:6791';
        $shm_key = ftok(__FILE__, 'c');
        $shmop = shmop_open($shm_key, "c", 0644, 8192);
        $data = [
            'datetime' => new \DateTime('2021-10-01 06:30:00.555'),
            'message' => 'Test message',
            'level' => LogLevel::CRITICAL,
        ];

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerUDP($address, $shmop);
            exit();
        }

        $pid = pcntl_fork();
        if ($pid === 0) {
            $writer = new GraylogWriter($address);
            $writer->write($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 8192));
        shmop_delete($shmop);

        $this->assertStringContainsString('Test message', $logged);
    }

    /**
     * @testdox Write 1 message in chunks (UDP)
     * @test
     */
    public function writeUDPChunks()
    {
        $address = 'udp://localhost:6791';
        $shm_key = ftok(__FILE__, 'c');
        $shmop = shmop_open($shm_key, "c", 0644, 8192);
        $data = [
            'datetime' => new \DateTime('2021-10-01 06:30:00.555'),
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam est mauris, aliquet ac nibh eget, faucibus sollicitudin neque. Pellentesque sed pulvinar ex, nec venenatis magna. Praesent molestie sed.',
            'level' => LogLevel::CRITICAL,
        ];

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerUDP($address, $shmop, 6);
            exit();
        }

        $pid = pcntl_fork();
        if ($pid === 0) {
            $writer = new GraylogWriter($address, 100);
            $writer->write($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 8192));
        shmop_delete($shmop);

        $this->assertStringContainsString('Lorem ipsum dolor sit amet', $logged);
        $this->assertStringContainsString('timestamp', $logged);
    }

    /**
     * @testdox  Write messages (TCP)
     * @test
     */
    public function processUDP()
    {
        $address = 'udp://localhost:6791';
        $shm_key = ftok(__FILE__, 'c');
        $shmop = shmop_open($shm_key, "c", 0644, 8192);

        $data = [
            [
                'datetime' => \DateTime::createFromFormat('U.u', microtime(true)),
                'message' => '10 Lorem ipsum dolor sit amet.',
                'level' => LogLevel::CRITICAL,
            ],
            [
                'datetime' => \DateTime::createFromFormat('U.u', microtime(true)),
                'message' => '20 Lorem ipsum dolor sit amet.',
                'level' => LogLevel::INFO,
            ],
        ];

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerUDP($address, $shmop, 2);
            exit();
        }

        $pid = pcntl_fork();
        if ($pid === 0) {
            $writer = new GraylogWriter($address);
            $writer->process($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 8192));
        shmop_delete($shmop);

        $this->assertStringContainsString('10 Lorem ipsum dolor sit amet.', $logged);
        $this->assertStringContainsString('20 Lorem ipsum dolor sit amet.', $logged);
    }
}
