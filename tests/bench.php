<?php
require __DIR__ . '/bootstrap.php';

use ExpressLogger\Formatter\JsonFormatter;
use ExpressLogger\Formatter\JsonPrettyFormatter;
use ExpressLogger\ExpressLogger;
use ExpressLogger\LoggingStrategy\StandardStrategy;
use ExpressLogger\Writer\FileWriter;


function expressLogger()
{
    //ini_set('memory_limit', '128M');
    $logger = new ExpressLogger(new FileWriter(TEST_LOG_FILE, new JsonFormatter(eol : "\n")));
    //$logger->setLoggingStrategy(new StandardStrategy());
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
expressLogger();
