<?php

use ExpressLogger\Formatter\LinePatternFormatter;
use ExpressLogger\Writer\FileWriter;
use ExpressLogger\Logger;

require __DIR__ . '/bootstrap.php';


ab();


function ab()
{
    expressLogger();
}
// 1040
function expressLogger()
{
    $formatter = new LinePatternFormatter(null,'%datetime% %level%: %message% %exception%'. "\n");
    $writer = new FileWriter(TEST_LOG_FILE, $formatter);
    //$writer->addFilter(new \ExpressLogger\Filter\LogLevelFilter(\Psr\Log\LogLevel::INFO, \Psr\Log\LogLevel::CRITICAL));
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
