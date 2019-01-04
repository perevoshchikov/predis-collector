<?php

namespace Anper\RedisCollector\Tests;

use Anper\RedisCollector\ConnectionInterface;
use Anper\RedisCollector\Format\CommandFormatterInterface;
use Anper\RedisCollector\Format\ResponseFormatterInterface;
use Anper\RedisCollector\RedisCollector;
use Anper\RedisCollector\Profile;
use Anper\RedisCollector\Format\Response\DefaultFormatter as ResponseFormatter;
use Anper\RedisCollector\Format\Command\DefaultFormatter as CommandFormatter;

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
                'map' => 'redis.nb_profiles',
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
        $mock1 = $this->createMock(ResponseFormatterInterface::class);
        $mock2 = $this->createMock(ResponseFormatterInterface::class);

        $this->collector->addResponseFormatter($mock1, 1);
        $this->collector->addResponseFormatter($mock2, 2);

        $this->assertContains([$mock1, 1], $this->collector->getResponseFormatters());
        $this->assertContains([$mock2, 2], $this->collector->getResponseFormatters());
    }

    public function testAddCommandFormatter()
    {
        $mock1 = $this->createMock(CommandFormatterInterface::class);
        $mock2 = $this->createMock(CommandFormatterInterface::class);

        $this->collector->addCommandFormatter($mock1, 1);
        $this->collector->addCommandFormatter($mock2, 2);

        $this->assertContains([$mock1, 1], $this->collector->getCommandFormatters());
        $this->assertContains([$mock2, 2], $this->collector->getCommandFormatters());
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

    public function testHasDefaultFormatters()
    {
        $this->assertContains([new ResponseFormatter(), 0], $this->collector->getResponseFormatters());
        $this->assertContains([new CommandFormatter(), 0], $this->collector->getCommandFormatters());
    }

    public function testCollect()
    {
        $this->assertCollect(new CommandFormatter(), new ResponseFormatter());
    }

    public function testCustomFormatters()
    {
        $response = $this->createMock(ResponseFormatterInterface::class);
        $response->expects($this->atLeastOnce())
            ->method('supports')
            ->will($this->returnValue(true));
        $response->expects($this->atLeastOnce())
            ->method('format')
            ->willReturn('response');

        $command = $this->createMock(CommandFormatterInterface::class);
        $command->expects($this->atLeastOnce())
            ->method('supports')
            ->will($this->returnValue(true));
        $command->expects($this->atLeastOnce())
            ->method('format')
            ->willReturn('command');

        $this->collector->addResponseFormatter($response);
        $this->collector->addCommandFormatter($command);

        $this->assertCollect($command, $response);
    }

    public function assertCollect(
        CommandFormatterInterface $commandFormatter,
        ResponseFormatterInterface $responseFormatter
    ) {
        $profile = new Profile('SET', ['key', 'value']);

        $startTime   = 0.2133;
        $stopTime    = 0.2163;
        $startMemory = 4595112;
        $stopMemory  = 4691048;

        $profile->start($startTime, $startMemory);
        $profile->end($stopTime, $stopMemory);
        $profile->setError('ERROR');
        $profile->setResponse('OK');

        $connection = $this->createMock(ConnectionInterface::class);

        $connection->expects($this->atLeastOnce())
            ->method('getProfiles')
            ->will($this->returnValue([$profile]));

        $connection->expects($this->atLeastOnce())
            ->method('getConnectionId')
            ->will($this->returnValue('connectionMock'));

        $this->collector->addConnection($connection);

        $result = $this->collector->collect();

        $expected = [
            'nb_profiles' => 1,
            'duration' => $profile->getDuration(),
            'memory' => $profile->getMemoryUsage(),
            'profiles' => [
                [
                    'prepared_profile' => $commandFormatter->format($profile),
                    'prepared_response' => $responseFormatter->format('OK'),
                    'duration' => $profile->getDuration(),
                    'duration_str' => $this->collector
                        ->getDataFormatter()
                        ->formatDuration($profile->getDuration()),
                    'memory' => $profile->getMemoryUsage(),
                    'memory_str' => $this->collector
                        ->getDataFormatter()
                        ->formatBytes($profile->getMemoryUsage()),
                    'is_success' => $profile->isSuccess(),
                    'error_message' => $profile->getError(),
                    'connection_id' => $connection->getConnectionId(),
                ]
            ],
            'duration_str' => $this->collector
                ->getDataFormatter()
                ->formatDuration($profile->getDuration()),
            'memory_str' => $this->collector
                ->getDataFormatter()
                ->formatBytes($profile->getMemoryUsage()),
        ];

        $this->assertEquals($expected, $result);
    }
}
