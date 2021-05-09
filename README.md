# ExpressLogger


ExpressLogger is a PSR3 implementation with a minimum impact on your application execution time. 

Implemented solution is simple but powerful and makes the ExpressLogger one of the fastest loggers. 

## Installation

 Composer

```bash
$ composer require expresslogger/expresslogger
```

## Usage

Basic usage. Use default logger settings to log a message into some file in json format.

```php
<?php
use ExpressLogger\Logger;
use ExpressLogger\Writer\FileWriter;
use ExpressLogger\Formatter\JsonPrettyFormatter;

$logger = new Logger( new FileWriter( __DIR__ . '/var/logs.log', new JsonPrettyFormatter()) );
$logger->debug('Debug message', [
    'client_login' => 'login@domain.com',
]);
```
This will produce the following result. Note that context data (`client_login` field) is simply merged into the default 
log data which is: `datetime`, `message` and `level`.

```json
{
  "datetime": {
    "date": "2021-05-05 17:56:16.622701",
    "timezone_type": 3,
    "timezone": "UTC"
  },
  "message": "Debug message",
  "level": "debug",
  "client_login": "login@domain.com"
}
```

## About

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/kderyabin/ExpressLogger/issues)

### License

Logger is licensed under the MIT License

