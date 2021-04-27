# PSR 3 Logger 

[![Build Status](https://travis-ci.org/kderyabin/logger.svg?branch=master)](https://travis-ci.org/kderyabin/logger)

Lightweight and easy customizable logger implementation of the [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) standard.


## Requirements
Logger works with PHP >=7.1.

## Installation
Install the latest version with composer

```bash
$ composer require kod/logger
```

## Usage

Basic usage. Use default logger settings to log a message into 'php://stderr' in json format. Perfect for docker environment and works out of the box.

```php
<?php
use ExpressLogger\Logger;

$log = new Logger();
$log->debug('Debug message', [
    'client_login' => 'login@domain.com',
]);
```
Note the context data (`client_login` field) is simply merged into the default data structure.

```json
{
  "message": "Debug message",
  "level": "debug",
  "level_code": 7,
  "datetime": "2018-11-07T19:10:33.757+01:00",
  "client_login": "login@domain.com"
}
```
Here is a little bit more advanced setup for logging into a file. In this example we extend a default log data structure with fields that must be appended to every log. Those fields, if not overridden with context data, will have a default value.

```php
<?php
use ExpressLogger\Logger;

$log = new Logger([
    'message' => [
        // extend default log's data
        'fields' => [
            'client_name' => '',
            'client_login' => '',
            'request_uri' => $_SERVER['REQUEST_URI'],
            'client_ip' => $_SERVER['REMOTE_ADDR'],
        ],
    ],
    // distribution channels: places where logs must be written 
    'channels' => [
        [
            'handler' => [
                'path' => '/var/tmp/debug.log'
            ],
        ],
    ],
]);

$log->debug('Debug message', [
    'client_login' => 'login@domain.com',
]);
```
And here is our log message:
```json
{
    "message": "Debug message",
    "level": "debug",
    "level_code": 7,
    "datetime": "2018-11-07T19:10:33.757+01:00",
    "client_name": "",
    "client_login": "login@domain.com",
    "request_uri": "/",
    "client_ip": "127.0.0.1"
}
```
## Documentation

* [Basics](./doc/core.md)
* [Settings](./doc/configuration.md)

## About

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/kderyabin/logger/issues)

### Author

Konstantin Deryabin - <kderyabin@orange.fr>

### License

Logger is licensed under the MIT License - see the `LICENSE` file for details

