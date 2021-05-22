<?php

use ExpressLogger\Formatter\JsonPrettyFormatter;
use ExpressLogger\ExpressLogger;
use ExpressLogger\Writer\FileWriter;

require __DIR__ . '/bootstrap.php';


(fn() => expressLogger())();

function expressLogger()
{
    ini_set('memory_limit', '5M');
    $logger = new ExpressLogger(new FileWriter(TEST_LOG_FILE, new JsonPrettyFormatter()));
    //$logger->setLoggingStrategy(new \ExpressLogger\LoggingStrategy\StandardStrategy());
    $eta = -hrtime(true);
    for ($i = 0; $i < 100_000; $i++) {
        $logger->info('Hello', ['exception' => 'is evil']);
    }
    $eta += hrtime(true);
    print_r([
        'memory_get_usage_false' => memory_get_usage(),
        'memory_get_usage_true' => memory_get_usage(true),
        'memory_get_peak_usage_false' => memory_get_peak_usage(false),
        'memory_get_peak_usage_true' => memory_get_peak_usage(true)
    ]);
    print_r([$eta / 1e+6]); //nanoseconds to milliseconds
}
