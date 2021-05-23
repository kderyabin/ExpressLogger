<?php

require_once  __DIR__ . '/../vendor/autoload.php';

const TEST_DIR = __DIR__;
const TEST_LOG_FILE = TEST_DIR . '/var/logs.log';
if(!is_file(TEST_LOG_FILE)) {
    touch(TEST_LOG_FILE);
}
