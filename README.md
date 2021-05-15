# ExpressLogger

ExpressLogger is a PSR3 implementation which cares of your application execution time. 
Implemented solution is simple but powerful and makes the ExpressLogger one of the fastest logging solutions.
## Installation

 Composer

```bash
$ composer require expresslogger/expresslogger
```

## Usage

Basic usage. Use default logger settings to log a message into some file in json format.

```php
<?php
use ExpressLogger\{Logger, Writer\FileWriter, Formatter\JsonPrettyFormatter};

$logger = new Logger( new FileWriter( __DIR__ . '/var/logs.log', new JsonPrettyFormatter()) );
$logger->debug('Debug message', [
    'client_login' => 'login@domain.com',
]);
```
This will produce the following result.

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
Note that context data (`client_login` field) is simply merged into the default log data which is: `datetime`, `message` and `level`.
## About

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/kderyabin/ExpressLogger/issues)

### License

Logger is licensed under the MIT License

