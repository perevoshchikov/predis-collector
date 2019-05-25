<?php

namespace Anper\PredisCollector\Tests;

use Anper\PredisCollector\PredisCollector;
use Anper\PredisCollector\Processor\ProcessorInterface;
use Anper\PredisCollector\Processor\ProviderInterface;
use Predis\ClientInterface;
use Predis\Command\StringSet;

/**
 * Class PredisCollectorTest
 * @package Anper\PredisCollector\Tests
 */
class PredisCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PredisCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new PredisCollector();
    }

    protected function tearDown()
    {
        $this->collector = null;
    }

    public function testGetWidgets()
    {
        $this->assertEquals([
            'predis' => [
                'icon' => 'align-justify',
                'widget' => 'PhpDebugBar.Widgets.PredisCommandsWidget',
                'map' => 'predis',
                'default' => '[]'
            ],
            'predis:badge' => [
                'map' => 'predis.nb_profiles',
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

    public function testGetName()
    {
        $this->assertEquals('predis', $this->collector->getName());

        $collector = new PredisCollector('custom');

        $this->assertEquals('custom', $collector->getName());
    }

    public function testAddClient()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('getConnection')
            ->willReturn('connection_id');

        $mock = $this->createMock(ProviderInterface::class);
        $mock->expects($this->once())
            ->method('register')
            ->with($client);

        $collector = new PredisCollector('redis', $mock);

        $collector->addClient($client);
    }

    public function testCollect()
    {
        $command = new StringSet();
        $command->setArguments(['foo', 'bar']);
        $clientName = 'default';

        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())
            ->method('getCommands')
            ->willReturn([$command]);

        $client = $this->createMock(ClientInterface::class);

        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())
            ->method('register')
            ->willReturn($processor)
            ->with($client);

        $collector = new PredisCollector('redis', $provider);

        $collector->addClient($client, $clientName);

        $this->assertEquals($collector->collect(), [
            'nb_profiles' => 1,
            'profiles' => [
                [
                    'method' => $command->getId(),
                    'arguments' => $command->getArguments(),
                    'connection' => $clientName,
                ],
            ]
        ]);
    }
}
