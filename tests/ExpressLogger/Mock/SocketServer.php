<?php

declare(strict_types=1);

namespace ExpressLogger\Tests\Mock;

use RuntimeException;
use Shmop;

class SocketServer
{
    public static function runServerTCP(string $address, Shmop $shmop): void
    {
        $socket = self::getServer($address);
        $keepOn = true;
        $cnt = 0;
        while (++$cnt < 5 && $keepOn) {
            $keepOn = self::listenTCP($socket, $shmop, 0.1);
        }
        fclose($socket);
    }

    public static function runServerUDP(string $address, Shmop $shmop, $expectedNbrMsg = 1): void
    {
        $socket = self::getServer($address);
        $cnt = 0;
        $message = '';
        while (++$cnt <= $expectedNbrMsg) {
            self::listenUDP($socket, $message);
        }
        if ($message) {
            shmop_write($shmop, serialize($message), 0);
        }
        fclose($socket);
    }


    protected static function getServer(string $address)
    {
        $isUDP = str_starts_with($address, 'udp:');
        $flag = $isUDP  ? STREAM_SERVER_BIND : STREAM_SERVER_BIND | STREAM_SERVER_LISTEN ;
        $socket = stream_socket_server($address, $errno, $errstr, $flag);
        stream_set_timeout($socket, 5);
        if (!$socket) {
            throw new RuntimeException("$errstr ($errno)");
        }

        return $socket;
    }

    protected static function listenTCP($socket, Shmop $shmop, float $timout): bool
    {
        if ($conn = @stream_socket_accept($socket, $timout)) {
            $message = fread($conn, 1024);
            shmop_write($shmop, serialize($message), 0);
            fclose($conn);
            return true;
        }
        return false;
    }

    protected static function listenUDP($socket, string &$msg): bool
    {
        $message = stream_socket_recvfrom($socket, 65536, 0);
        if ($message) {
            $msg .= $message;
            return true;
        }
        return false;
    }
}
