# XLog
Custom logger for PHP exceptions/errors/warnings/notices.

## Installation
* Using composer:
```
composer config --append repositories.xlog vcs https://github.com/Xeloses/xlog
composer require xeloses/xlog
```
or
```
composer config --append repositories.xlog vcs https://github.com/Xeloses/xlog
composer require xeloses/xlog --dev
```

## Usage
Register errors/exception handler:
```php
use Xeloses\XLog\XLog;

void XLog::register(?ILogOutputProvider $provider, int $log_level, bool $debug)
```
* `$provider` - log output provider:
    * `LogScreen` - output log to browser;
    * `LogFile` - output log to file;
    * any custom implementation of `Xeloses\XLog\Interfaces\ILogOutputProvider`.
* `$log_level` - combination of PHP LOG_LEVEL constants or:
    * `XLog::DEFAULT` - equals to `E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED`;
    * `XLog::ALL` - all errors/warnings/notices *(include released in future)*;
    * `XLog::NONE` - none;
    * `XLog::DEBUG` - equals to `XLog::ALL`;
    * `XLog::ERRORS` - equals to `E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_USER_ERROR`;
    * `XLog::WARNINGS` - equals to `E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING`;
    * `XLog::NOTICES` - equals to `E_NOTICE | E_STRICT | E_DEPRECATED | E_USER_NOTICE | E_USER_DEPRECATED`.
    > Default value is `XLog::DEFAULT`.
* `$debug` - debug mode, it automatically set `$log_level` to `XLog::DEBUG` and allow to use `XLog::dump` method.
    * Another way to go debug mode is: 
        ```php
        define('DEBUG',true);
        XLog::register();
        ```
        When `DEBUG` is defined and set to `true` method `XLog::register()` can be called without arguments. It will automatically use `LogScreen` as output provider *(but you still can pass your own provider)* and set `$debug` attribute to `true`.
    > Default value is `false`.

This method calls
```php
ini_set('display_errors',0);
error_reporting(0);
```
itself. All notices, warnings, errors *(except fatal)* and exceptions will be processed inside this class and passed to output provider.

### To output log to browser:
```php
XLog::register(new LogScreen(['timestamp_format' => 'c']), XLog::ALL);
```
or
```php
define('DEBUG',true);
XLog::register();
```

Options:
* `timestamp_format` - format of timestamps in log output *(see format of PHP [date()](https://www.php.net/manual/ru/function.date.php#refsect1-function.date-parameters) function)*.
    > Default value is `'c'` - ISO 8601 datetime.

### To output log to file:
```php
XLog::register(
    new LogFile([
        'timestamp_format' => 'c',
        'filename'  => '/logs/{yyyy}-{mm}-{dd}.{n}.log',
        'filesize' => 5*1024*1024,
        'overwrite' => false,
    ]),
    XLog::ERRORS
);
```
Options:
* `timestamp_format` - format of timestamps in log output *(see format of PHP [date()](https://www.php.net/manual/ru/function.date.php#refsect1-function.date-parameters) function)*. 
    > Default value is `'c'` - ISO 8601 datetime.
* `filename` - log file name (with path). Available placeholders:
    * `{date}` - current date, equals to PHP `date('Y-m-d')`;
    * `{year}` or `{Y}` or `{yyyy}` - year, 4 digits, equals to PHP `date('Y')`;
    * `{yy}` - year, 2 digits, equals to PHP `date('y')`;
    * `{Month}` - month name, full  *(January, Febrary, ...)*, equals to PHP `date('F')`;
    * `{month}` or `{M}` - month name, short *(Jan, Feb, ...)*, equals to PHP `date('M')`;
    * `{mm}` - month, 2 digits, equals to PHP `date('m')`;
    * `{m}` - month, 1 or 2 digits, equals to PHP `date('n')`;
    * `{Day}` - day name, full *(Sunday, Monday, ...)*, equals to PHP `date('l')`;
    * `{day}` or `{D}` - day name, short *(Sun, Mon, ...)*, equals to PHP `date('D')`;
    * `{dd}` - day, 2 digits, equals to PHP `date('d')`;
    * `{d}` - day, 1 or 2 digits, equals to PHP `date('j')`;
    * `{n}` - counter *(usable only when `overwrite` option is `TRUE`)*.
    > Default value is `./logs/{date}.log`.
* `filesize` - maximum size of log file in bytes, when lof file reach this size it will be overwritten *(if `overwrite` option is `true`)* or new log file will be created *(if `overwrite` option is `false`)*.
    > Default value is `1048576` = 1 Mb.
* `overwrite` - overwrite log file when it reach max size or not *(see description of `filesize` option above)*
    > Default value is `true`.
    
> **WARNING**: Directories for log created by class will have `0777` access mode. Log files created by class will have `0775` access mode. Path and/or filename should be added to `.htaccess` manually to close it from public access!

## Write to log.
To write some custom message to log use:
```php
void XLog::log(string $message)
```
Also you can output to log with PHP function [trigger_error()](https://www.php.net/manual/ru/function.trigger-error.php).
> *Note: `$error_type` passed to `trigger_error()` should match `$error_level` passed to `XLog::register()`.*

If debug mode enabled you can dump variables to log:
```php
void XLog::dump(mixed $var, ?string $comment = '')
```

## Example
```php
<?php
define('DEBUG',true);

use Xeloses\XLog\XLog;

XLog::register();
XLog::log('A custom message');

$array_example = [
	'first' => 1,
	'second' => '2nd'
];
XLog::dump($array_example, 'Comment for dump of array');

trigger_error('User notice generated with trigger_error()',E_USER_NOTICE);
trigger_error('User warning generated with trigger_error()',E_USER_WARNING);
throw new Exception('Exception example.');
?>
```
Output:

![Preview](https://github.com/Xeloses/xlog/raw/master/preview_logscreen.jpg)
