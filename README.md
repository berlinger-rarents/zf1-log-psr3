# PSR-3 compliant wrapper for Zend Framework 1's Logger

Did you ever want to use your good old (fashioned) [ZF1 logger](https://framework.zend.com/manual/1.12/en/zend.log.html)
 in a modern loosely coupled library that expects a [PSR-3](http://www.php-fig.org/psr/psr-3/) compliant logger?
Then this is the library for you. You can keep your existing logger (e.g. to share with other components of your
 software) and disguise it to feed it to the new library.

## Features

* PSR-3 style placeholders are rewritten to ZF1 style.
   E.g. `{example}` becomes `%example%`.
   (Can be disabled.)
* `logLevel` is added to the `extras` cq context array.
   (Can be disabled.)
* (Not directly related to the goal of this library, but useable for testing:) A very basic implementation of
   `Zend_Log_Writer_Abstract` is available: [`MemoryWriter`](src/MemoryWriter.php).
   It keeps the formatted logged messages in memory, to be retrieved later.

## Usage

Require [this package](https://packagist.org/packages/boerl/zf1-log-psr3)
 with [composer](https://getcomposer.org/)
 and [use its autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading).

```php
// Start with your existing instance of Zend_Log.
$myZendLog = new \Zend_Log();

// Optionally create a configuration array.
$optionalConfig = ['foo' => 'bar'];

// Instantiate the Wrapper from the existing Zend_Log and optionally the configuration.
$myPsr3CompliantLogger = new \Boerl\Zf1LogPsr3\Wrapper($myZendLog, $optionalConfig);
```

For available configuration options, look at [`Wrapper::getDefaultConfig()`](src/Wrapper.php#L40).

## Links

* [michaelmoussa/zend-psr-log](https://github.com/michaelmoussa/zend-psr-log)  
  Similar concept, but for [ZF2's Logger](https://github.com/zendframework/zend-log). 
* [InterNations/ZendLogAdapterPsr3](https://github.com/InterNations/ZendLogAdapterPsr3)  
  Similar concept, but reversed.