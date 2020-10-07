[![Latest Version](https://img.shields.io/packagist/v/wol-soft/php-performance-timer.svg)](https://packagist.org/packages/wol-soft/php-performance-timer)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)](https://php.net/)
[![Maintainability](https://api.codeclimate.com/v1/badges/2239ba208925a45e59c3/maintainability)](https://codeclimate.com/github/wol-soft/php-performance-timer/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/2239ba208925a45e59c3/test_coverage)](https://codeclimate.com/github/wol-soft/php-performance-timer/test_coverage)
[![Build Status](https://travis-ci.com/wol-soft/php-performance-timer.svg?branch=master)](https://travis-ci.com/wol-soft/php-performance-timer)
[![Coverage Status](https://coveralls.io/repos/github/wol-soft/php-performance-timer/badge.svg?branch=master)](https://coveralls.io/github/wol-soft/php-performance-timer?branch=master)
[![MIT License](https://img.shields.io/packagist/l/wol-soft/php-performance-timer.svg)](https://github.com/wol-soft/php-performance-timer/blob/master/LICENSE)

# php-performance-timer
Provides functions to collect processing times inside your application

## Requirements ##

- Requires at least PHP 7.1

## Installation ##

The recommended way to install php-json-schema-model-generator is through [Composer](http://getcomposer.org):
```
$ composer require wol-soft/php-performance-timer
```

## Usage

To start a timer simply call the `start` method with a key. The key will be used to identify the timer:

```php
Timer::start('my-timer');
```

Finish the timer with the `end` method:

```php
Timer::end('my-timer');
```

By default, the timer measurements of a process will be collected and written to `/tmp/performance_timer.log` (may vary if called from apache as `sys_get_temp_dir` is used by default).
If you want to fetch the results manually use `Timer::handleResults`.

The result will be a csv with the timer key and the duration between `start` and `end` (in ms):

```csv
my-timer,12.1324
my-timer,14.5271
my-timer,11.7832
...
```

### Namespaced timers

Each `start` and `end` method call takes an optional second parameter `$namespace`. By providing namespaces to your timers you can enable/disable measurements in specific components.

```php
Timer::initSettings(['profileNamespace' => 'component.booking']);

...

Timer::start('login', 'component.user');
...
Timer::end('login', 'component.user');

...

Timer::start('check-basket', 'component.booking.init');
...
Timer::end('check-basket', 'component.booking.init');
```

Only the timers with namespaces starting with the configured namespace are executed. Timers without a namespace will always be executed. If the option `profileNamespace` is set to false no timer will be executed.

### Exceptions

By default the timer execution may throw exceptions (eg. if a timer is started twice). If you don't want the timer to break your execution flow you can set the option `throwExceptions` to false. In this case the timer will simply ignore invalid calls.

```php
Timer::initSettings(['throwExceptions' => false]);
```

### Custom data collection

To collect additional data (eg. memory consumption, start and end timestamps, ...), you can add a timer plugin:

```php
Timer::addTimerPlugin($callbackStart, $callbackEnd);
```

The data returned by `$callbackStart` will be passed to `$callbackEnd`. The data returned by `$callbackEnd` will be included in the generated CSV file. By returning an array from `$callbackEnd` you can add multiple columns to the CSV.
