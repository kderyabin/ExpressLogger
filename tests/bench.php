<?php

use ExpressLogger\Formatter\JsonPrettyFormatter;
use ExpressLogger\Logger;
use ExpressLogger\Writer\FileWriter;

require __DIR__ . '/bootstrap.php';


ab();

function ab()
{
    expressLogger();
}

function expressLogger()
{
    //ini_set('memory_limit', '128M');
    $logger = new Logger( new FileWriter(TEST_LOG_FILE, new JsonPrettyFormatter()));
    $logger->setExpressMode(true);
    $eta = -hrtime(true);
    for ($i = 0; $i < 100_000; $i++) {
        $logger->info('Hello', ['index' => $i, 'exception' => 'is evil',  'memory' => memory_get_usage(true)]);
    }

    $eta += hrtime(true);
    print_r( [
        'isExpressMode' => $logger->isExpressMode(),
        'buffer' => $logger->getBufferSize(),
        'memory_get_usage_false' => memory_get_usage(),
        'memory_get_usage_true' => memory_get_usage(true),
        'memory_get_peak_usage_false' => memory_get_peak_usage(false),
        'memory_get_peak_usage_true' => memory_get_peak_usage(true)
    ]);
    print_r([$eta / 1e+6]); //nanoseconds to milliseconds
}
