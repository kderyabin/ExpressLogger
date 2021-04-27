<?php

use ExpressLogger\Formatter\LinePatternFormatter;
use ExpressLogger\Writer\FileWriter;
use ExpressLogger\Logger;

require __DIR__ . '/bootstrap.php';


ab();


function ab()
{
    metrolog();
}

function metrolog()
{
    $formatter = new LinePatternFormatter(null,'%datetime% %level%[%level_code%]: %message% %exception%'. "\n");
    $writer = new FileWriter(TEST_DIR . '/logs.log', $formatter);
    $logger = new Logger();
    $logger->setIsTurbo(false);
    $logger->addWriter($writer);

    $eta = -hrtime(true);
    for ($i = 0; $i < 100000; $i++) {
        $logger->info('Hello', ['exception' => 'is evil']);
    }

    $eta += hrtime(true);

    print_r([$eta / 1e+6]); //nanoseconds to milliseconds
}
