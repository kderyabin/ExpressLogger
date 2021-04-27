# Settings

## Logger

`levelCode`     _array_  
> Associative array with levels as a keys and codes as values. 

 Example
```php
$logger = new Logger([
    'levelCode' => [
            'debug' => 100, 
            'info' => 200, 
            etc...
        ],
    ],
);
```
Important: If you need custom codes you must declare them for all 8 levels.

`levelPriorityMax`   _string_ 
> Higest level priority to be logged. Messages with priority higher then defined by the levelPriorityMax are ignored. 
Can be declared individually per channel or/and globally for all channels.  

```php
$logger = new Logger([
    'levelPriorityMax' => 'alert',
    ],
);
```

`levelPriorityMin`   _string_ 
> Lowest level priority to be logged. Messages with priority lower then the levelPriorityMax are ignored. 

```php
$logger = new Logger([
    'levelPriorityMin' => 'info',
    ],
);
```

`message`          _array_
> Associative array of settings to create a Message object. See **Message** section.

`channels`          _array_
> Array of channels. Each item of the array can be an instance of the Channel object or an array of settings used to create a Channel object. See **Channels** section.

## Message

`instance` _string_ 
> Fully qualified class name to use for the Message creation. The default one is `ExpressLogger\Message`. You can extend this class to implement your business logic.

Ex.:
```php
$logger = new Logger([
        'message' => [
            'instance' => Message::class,
        ]    
    ],
);
```
`fields` _array_ 
> Associative array of custom fields with default values that will be appended to every log. 
Values of message fields can be overridden with arguments passed as a context array.
```php
$logger = new Logger([
     'message' => [
            'fields' => [
                'client_ip' => $_SERVER['REMOTE_ADDR'],
            ]
        ],
    ],
);
```
`dates`  _array_
> Associative array containing a field name as a key and date format as a value. Default log data structure has `datetime` field which is set `DATE_RFC3339_EXTENDED` constant. You can change it here and optionally configure other fields.
For date formatting see [php date function](http://php.net/manual/en/function.date.php) and [predefined date constants](http://php.net/manual/en/class.datetimeinterface.php#datetime.constants.types).

Important: date fields must be also declared in `fields` section.
```php
$log = new Logger([
    'message' => [
        'fields' => [
            'timestamp' => '',
        ],
        'dates' => [
            'datetime' => 'H:i:s m/d/y',
            'timezone' => 'T', // Timezone abbreviation
        ]
    ],
]);
```

`setters` _array_
> Associative array containing field name as a key and a setter as value. The setter allows to modify a field's value. It may be an anonymous function or a callable accepting 1 argument and returning a new value.

```php
$log = new Logger([
    'message' => [
        'setters' => [
            // encrypt a login
            'login' => ['MyClass', 'encrypt'],
            // obfuscate a mobile number
            'mobile' => function($value){
                return  substr_replace($value,  '****', 2, -3);
            }
        ]
    ],
]);
```
`filters` _array_
> Array of callables. A filter operates on all fields and its main purpose is to prepare the data for logging. Like the setter it may be an anonymous function or a callable accepting 1 argument, an array, and returning transformed array.

```php
$log = new Logger([
    'message' => [
        'filters' => [
            // remove empty fields
            function($fields){
                return array_filter($fields, function($value){
                    return !empty($value);
                });
            }
        ],
    ],
]);
``` 
## Channels

As said above each item of the `channels` array can be an instance of the Channel object or an array of settings used to create a Channel object. 

### Channel
`levelPriorityMax`   _string_ 
> Highest level priority to be logged with current channel.

`levelPriorityMin`   _string_ 
> Lowest level priority to be logged with current channel.

```php
$logger = new Logger([
        'channels' => [
            [
                'levelPriorityMax' => 'alert',
                'levelPriorityMin' => 'info',
            ],
        ],
    ],
);
```
`handler` _array_
> Associative array of options used to instantiate a handler object. See **Handler** section for more details.

`formatter` _array_
> Associative array of options used to instantiate a formatter object. See **Formatter** section for more details.

#### Handler
`instance` _string_ 
> Fully qualified class name to use for the handler instantiation. The default handler is `ExpressLogger\Handlers\StreamHandler`. You can omit `instance` declaration for the default handler.

Note: all settings except `instance` are passed to chosen handler as options.

#### Formatter
`instance` _string_ 
> Fully qualified class name to use for the formatter instantiation. The default formatter is `ExpressLogger\Formatters\JsonFormatter`. You can omit `instance` declaration for the default formatter.

Note: all settings except `instance` are passed to chosen formatter as options.

```php
use ExpressLogger\Writer\SyslogWriter;
use ExpressLogger\Formatter\LinePatternFormatter;

$logger = new Logger([
        'channels' => [
            [
             'handler' => [
                    'instance' => SyslogWriter::class,
                    'sysIdent' => 'backend',
                    'sysOptions' => LOG_ODELAY | LOG_PID,
                    'sysFacility' => LOG_USER
                ],
                'formatter' => [
                    'instance' => LinePatternFormatter::class,
                    'format' => '{datetime} {level} {level_code} {message}',
                ],
            ],
        ],
    ],
);
```



## Example

Here is an example of advanced configuration. We define our log data structure and also some setters and filters to apply to that data before writing it. We declare 2 distribution channels where logs will be sent in different formats.

```php
<?php

use ExpressLogger\Logger;
use ExpressLogger\Writer\SyslogWriter;
use ExpressLogger\Formatter\LinePatternFormatter;

$log = new Logger([
    'message' => [
        // declare your fields and their default values
        'fields' => [
            'client_name' => 'NA',
            'client_login' => '',
            'request_uri' => $_SERVER['REQUEST_URI'],
            'client_ip' => $_SERVER['REMOTE_ADDR'],
            'timezone' => '',
        ],
        // optional, only if you need an extra date fields or override existing format 
        // indicate a field name and a date format
        'dates' => [
            'datetime' => 'Y-m-d H:i:s', // override default datetime format
            'timezone' => 'T', // Timezone abbreviation
        ],
        // optional, (re)set fields values
        // can be an anonymous function or a callback ex.: ['MyClass', 'encrypt']
        'setters' => [
            // imagine the security team wants it to be hashed
            'client_login' =>  function($value){
                return md5($value);
            },
        ],
        // optional, filter final log data
        // can be an anonymous function or a callback ex.: ['MyClass', 'encrypt']
        'filters' => [
            // this one is for devops because you log too much useless data, from their point of vue of course
            // remove empty fields and fields with default values
            function($fields){
                return array_filter($fields, function($value){
                    return !empty($value) && $value !== 'NA';
                });
            }
        ]
    ],
    // now let's declare our log distribution channels
    'channels' => [
        // Development channel for logging anything
        [
            // here we use a default handler which is a FileWriter
            'handler' => [
                'path' => 'php://stdout'
            ],
        ],
        // Devops' channel for monitoring the application health state
        [
            'levelPriorityMin' => 'warning',
            // here we use a SyslogWriter to log to syslog
            'handler' => [
                'instance' => SyslogWriter::class,
                // SyslogWriter options: see SyslogWriter class for available options
                'sysIdent' => 'frontend',
                'sysOptions' => LOG_ODELAY | LOG_PID,
                'sysFacility' => LOG_USER
            ],
            'formatter' => [
                'instance' => LinePatternFormatter::class,
                // LinePatternFormatter options: see LinePatternFormatter class for available options
                'format' => '[{level} ({level_code})] [{datetime} ({timezone})] [{client_ip}] [{request_uri}] [{message}'],
            ]
        ]
    ]
]);
$log->warning('Waouh! Something is going here', [
    'client_login' => 'login@domain.com',
]);

```
Let's see now how our logs look like. The development channel will produce a log in json format because it's the default one. Notice, the `client_login` field's value is hashed and `client_name` field is not present because it did not passed a filter. 

```json
{
    "message": "Waouh! Something is going here",
    "level": "warning",
    "level_code": 4,
    "datetime": "2018-11-07 19:20:43",
    "client_login": "36b9fcdb7337dd4e3453cf3a4ba575c4",
    "request_uri": "/",
    "client_ip": "127.0.0.1",
    "timezone": "CET"
}
```
And the same log message done with the SyslogHandler and the LinePatternFormatter will look like that
```bash
Nov  7 19:20:43 Konst-W10 frontend[953]: [warning (4)] [2018-11-07 19:20:43 (CET)] [127.0.0.1] [/] [Waouh! Something is going here]
```