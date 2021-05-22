# ExpressLogger

ExpressLogger is a PSR3 implementation which cares of the execution time of your application. 
Implemented solution is not new but simple and powerful and makes the ExpressLogger one of the fastest logging solutions.

# Why it's so fast ?

ExpressLogger is designed to have the least possible impact on the performance of your application.

# How it works

ExpressLogger comes with 2 modes : **express** (default mode) and **standard**.

## Standard Mode

In **Standard** mode the logger writes logs in a conventional way. It applies filters, formats the message and then sends it to the destination.
All these operations have a cost, and the cost is the execution time of your app.

## Mode Express (default)

In **Express** mode, to reduce the processing time of a message (filtering, formatting, writing), the logger delays logs writing  
until the end of the execution of the application.  

## Installation

 Composer

```bash
$ composer require expresslogger/expresslogger
```

## Usage

Basic usage. Use default logger settings to log a message into some file in json format.

```php
<?php
use ExpressLogger\{ExpressLogger, Writer\FileWriter, Formatter\JsonPrettyFormatter};

$logger = new ExpressLogger( new FileWriter( __DIR__ . '/var/logs.log', new JsonPrettyFormatter()) );
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

