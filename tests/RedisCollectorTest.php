<?php

namespace Anper\RedisCollector\Tests;

use Anper\RedisCollector\ConnectionInterface;
use Anper\RedisCollector\Format\DefaultFormatter;
use Anper\RedisCollector\Format\FormatterInterface;
use Anper\RedisCollector\RedisCollector;
use Anper\RedisCollector\Statement;

/**
 * Class RedisCollectorTest
 * @package Anper\RedisCollector\Tests
 */
class RedisCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RedisCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new RedisCollector();
    }

    protected function tearDown()
    {
        $this->collector = null;
    }

    public function testGetWidgets()
    {
        $this->assertEquals([
            'redis' => [
                'icon' => 'align-justify',
                'widget' => 'PhpDebugBar.Widgets.RedisQueriesWidget',
                'map' => 'redis',
                'default' => '[]'
            ],
            'redis:badge' => [
                'map' => 'redis.nb_statements',
                'default' => 'null'
            ]
        ], $this->collector->getWidgets());
    }

    public function testGetAssets()
    {
        $assets = $this->collector->getAssets();

        $this->assertArrayHasKey('css', $assets);
        $this->assertArrayHasKey('js', $assets);

        $this->assertFileEquals(__DIR__ . '/../resources/css/widget.css', $assets['css']);
        $this->assertFileEquals(__DIR__ . '/../resources/js/widget.js', $assets['js']);
    }

    public function testAddConnection()
    {
        $mock1 = $this->createMock(ConnectionInterface::class);
        $mock2 = $this->createMock(ConnectionInterface::class);

        $this->collector->addConnection($mock1);
        $this->collector->addConnection($mock2);

        $this->assertContains($mock1, $this->collector->getConnections());
        $this->assertContains($mock2, $this->collector->getConnections());
    }

    public function testAddResponseFormatter()
    {
        $mock1 = $this->createMock(FormatterInterface::class);
        $mock2 = $this->createMock(FormatterInterface::class);

        $this->collector->addResponseFormatter($mock1, 1);
        $this->collector->addResponseFormatter($mock2, 2);

        $this->assertContains([$mock1, 1], $this->collector->getResponseFormatters());
        $this->assertContains([$mock2, 2], $this->collector->getResponseFormatters());
    }

    public function testGetName()
    {
        $this->assertEquals('redis', $this->collector->getName());

        $collector = new RedisCollector(null, 'custom');

        $this->assertEquals('custom', $collector->getName());
    }

    public function testAddConnectionFromConstruct()
    {
        $mock = $this->createMock(ConnectionInterface::class);

        $collector = new RedisCollector($mock);

        $this->assertContains($mock, $collector->getConnections());
    }

    public function testAddFormatterFromConstruct()
    {
        $mock = $this->createMock([ConnectionInterface::class, FormatterInterface::class]);

        $collector = new RedisCollector($mock);

        $this->assertContains($mock, $collector->getConnections());
        $this->assertContains([$mock, 10], $collector->getResponseFormatters());
    }

    public function testHasDefaultFormatter()
    {
        $this->assertContains([new DefaultFormatter(), 0], $this->collector->getResponseFormatters());
    }

    public function testCollect()
    {
        $statement = new Statement('SET', ['key', 'value']);

        $startTime   = 0.2133;
        $stopTime    = 0.2163;
        $startMemory = 4595112;
        $stopMemory  = 4691048;

        $response = 'OK';

        $connection = $this->createMock(ConnectionInterface::class);
        $connection->expects($this->any())
            ->method('getExecutedStatements')
            ->will($this->returnValue([$statement]));
        $connection->expects($this->any())
            ->method('getConnectionId')
            ->will($this->returnValue('connectionMock'));

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));
        $formatter->expects($this->any())
            ->method('format')
            ->will($this->returnArgument(0));

        $statement->start($startTime, $startMemory);
        $statement->end(new \Exception('exception'), $stopTime, $stopMemory);
        $statement->setResponse($response);

        $this->collector->addConnection($connection);
        $this->collector->addResponseFormatter($formatter);

        $result = $this->collector->collect();

        $expected = [
            'nb_statements' => 1,
            'duration' => $statement->getDuration(),
            'memory' => $statement->getMemoryUsage(),
            'statements' => [
                [
                    'prepared_stmt' => 'SET key value',
                    'prepared_response' => $formatter->format($response),
                    'duration' => $statement->getDuration(),
                    'duration_str' => $this->collector
                        ->getDataFormatter()
                        ->formatDuration($statement->getDuration()),
                    'memory' => $statement->getMemoryUsage(),
                    'memory_str' => $this->collector
                        ->getDataFormatter()
                        ->formatBytes($statement->getMemoryUsage()),
                    'is_success' => $statement->isSuccess(),
                    'error_message' => $statement->getErrorMessage(),
                    'connection_id' => $connection->getConnectionId(),
                ]
            ],
            'duration_str' => $this->collector
                ->getDataFormatter()
                ->formatDuration($statement->getDuration()),
            'memory_str' => $this->collector
                ->getDataFormatter()
                ->formatBytes($statement->getMemoryUsage()),
        ];

        $this->assertEquals($expected, $result);
    }
}
