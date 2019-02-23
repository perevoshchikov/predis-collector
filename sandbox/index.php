<?php

// run: composer start

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

$redis = new \Predis\Client([
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

try {
    $redis->connect();
} catch (\Exception $exception) {
    echo 'Failed connection to redis';

    exit(1);
}

$debugbar = new \DebugBar\DebugBar();

$collector = new \Anper\PredisCollector\PredisCollector();
$adapter = new \Anper\PredisCollector\PredisAdapter($collector);
$adapter->addClient($redis);

$debugbar->addCollector($collector);

$redis->set('foo', 'bar');
$redis->get('foo');
$redis->del(['foo']);
$redis->hmset('hash', ['one', 'two', '']);

$debugbar->collect();

$renderer = $debugbar->getJavascriptRenderer();

$uri = $_SERVER['REQUEST_URI'];

$root = \realpath(getcwd());

$files = [
    $root . '/resources/js/widget.js',
    $root . '/resources/css/widget.css',
];

if ($uri === '/') {
    echo <<<HTML
<html lang="en">
    <head>
        <title>Predis collector sandbox</title>
        {$renderer->renderHead()}
    </head>
    <body>
        <h1>Predis collector sandbox</h1>
        {$renderer->render()}
    </body>
</html>
HTML;

    return true;
} elseif (\in_array($uri, $files, true)) {
    echo \file_get_contents($uri);

    return true;
}

return false;
