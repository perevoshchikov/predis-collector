# Anper\RedisCollector

[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-coverage]][link-coverage]

Redis collector for [PHP Debugbar](https://github.com/maximebf/php-debugbar). Supports [predis/predis](https://github.com/nrk/predis).

![Screenshot](https://raw.githubusercontent.com/perevoshchikov/redis-collector/master/screenshot.png)

## Install

``` bash
$ composer require anper/redis-collector
```
if you have not installed predis:

``` bash
$ composer predis/predis
```

## Usage

``` php
use Anper\RedisCollector\Adapter\Predis\PredisAdapter;
use Anper\RedisCollector\RedisCollector;
use Predis\Client;

$client = new Client(...);

$collector = new RedisCollector();

$adapter = new PredisAdapter($collector);
$adapter->addClient($client);

$debugbar->addCollector($collector);
```

## Formatters
```php
// response formatters

use Anper\RedisCollector\Format\Respose\ArrayFormatter;
use Anper\RedisCollector\Format\Respose\StringFormatter;

$collector->addResponseFormatter(new ArrayFormatter());
$collector->addResponseFormatter(new StringFormatter());


// command formatters

use Anper\RedisCollector\Format\Command\HighlightFormatter;

$collector->addCommandFormatter(new HighlightFormatter());

```

## Test

``` bash
$ composer test
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email anper3.5@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/anper/redis-collector.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/perevoshchikov/redis-collector/master.svg?style=flat-square
[ico-coverage]: https://img.shields.io/coveralls/github/perevoshchikov/redis-collector/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/anper/redis-collector
[link-travis]: https://travis-ci.org/perevoshchikov/redis-collector
[link-coverage]: https://coveralls.io/github/perevoshchikov/redis-collector?branch=master
