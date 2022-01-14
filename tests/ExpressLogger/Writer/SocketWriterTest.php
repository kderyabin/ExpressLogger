<?php

namespace ExpressLogger\Tests\Writer;

use ExpressLogger\Tests\Mock\SocketServer;
use ExpressLogger\Writer\SocketWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class SocketWriterTest extends TestCase
{
    /** @test */
    public function writeTCP()
    {
        $address = 'tcp://localhost:6789';
        $shm_key = ftok(__FILE__, 't');
        $shmop = shmop_open($shm_key, "c", 0644, 1024);

        $pid = pcntl_fork();
        if ($pid === 0) {
            SocketServer::runServerTCP($address, $shmop);
            exit();
        }
        $pid = pcntl_fork();
        if ($pid  === 0) {
            $writer = new SocketWriter($address);
            $data = [
                'datetime' => new \DateTime('2021-10-01 06:30:00.555'),
                'message' => 'Test message',
                'level' => LogLevel::CRITICAL,
            ];
            $writer->write($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 1024));
        shmop_delete($shmop);

        $this->assertStringContainsString('Test message', $logged);
    }
    /** @test */
    public function writeUDP()
    {
        $address = 'udp://localhost:45182';
        $shm_key = ftok(__FILE__, 'u');
        $shmop = shmop_open($shm_key, "c", 0644, 1024);

        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->fail("Error forking server");
        } elseif ($pid == 0) {
            SocketServer::runServerUDP($address, $shmop);
            exit();
        }
        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->fail("Error forking server");
        } elseif ($pid == 0) {
            $writer = new SocketWriter($address);
            $data = [
                'datetime' => new \DateTime('2021-10-01 06:30:00.555'),
                'message' => 'Test message',
                'level' => LogLevel::CRITICAL,
            ];
            $writer->write($data);
            exit();
        }
        while (pcntl_waitpid(0, $status) != -1);
        $logged = unserialize(shmop_read($shmop, 0, 1024));
        shmop_delete($shmop);

        $this->assertStringContainsString('Test message', $logged);
    }
}
