# ExpressLogger

ExpressLogger is a PSR3 implementation which cares of the execution time of your application. 
Implemented solution is not new but simple and powerful and makes the ExpressLogger one of the fastest logging solutions.

# Why it's so fast ?

ExpressLogger is designed to have the least possible impact on the performance of your application.

# How it works

## Logging stratgey

ExpressLogger comes with 2 modes or logging strategies : **express** (default mode) and **standard**. By the way, you 
can implement and use your own strategy. 

### Standard Mode

In **Standard** mode the logger writes logs in a conventional way. It applies filters, formats the message and then 
sends it to the destination. All these operations have a cost, and the cost is the execution time of your app.

### Express Mode (default)

In **Express** mode, to reduce the processing time of a message (filtering, formatting, writing), the logger delays 
logs writing till the end of the execution of your app. On application shutdown it flushes the emitted content to the 
user and starts logs processing. Express logging strategy reduces drastically the impact on your 
application's performance.    

## Installation

 Composer

```bash
$ composer require expresslogger/expresslogger
```

## Usage

Basic usage. Use default logger settings to log a message into some file in json format.

```php
<?php

use ExpressLogger\{ExpressLogger, Writer\FileWriter, Formatter\JsonPrettyFormatter, Filter\LogLevelFilter};
use Psr\Log\LogLevel;

$writer = new FileWriter(__DIR__ . '/logs.log', new JsonPrettyFormatter());
$writer->addFilter(new LogLevelFilter(LogLevel::INFO));

$logger = new ExpressLogger($writer,  [ 'host' => $_SERVER['SERVER_NAME'] ?? 'localhost'] );

$logger->info('User login', ['client_login' => 'login@domain.com' ]);
```
This will produce the following result.

```json
{
  "datetime": {
    "date": "2021-05-05 17:56:16.622701",
    "timezone_type": 3,
    "timezone": "UTC"
  },
  "message": "User login",
  "level": "info",
  "host": "localhost",
  "client_login": "login@domain.com"
}
```
Note that context data (`client_login` field) is simply merged into the default log data which is: `datetime`, `message` and `level`.
## About

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/kderyabin/ExpressLogger/issues)

### License

Logger is licensed under the MIT License

