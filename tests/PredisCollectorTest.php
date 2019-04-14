<?php

namespace Anper\PredisCollector\Tests;

use Anper\Predis\CommandCollector\CollectorData;
use Anper\Predis\CommandCollector\CollectorInterface;
use Anper\PredisCollector\PredisCollector;
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

        $mock = $this->createMock(CollectorInterface::class);
        $mock->expects($this->once())
            ->method('addClient')
            ->with($client, 'default');

        $collector = new PredisCollector('redis', $mock);

        $collector->addClient($client, 'default');
    }

    public function testCollect()
    {
        $command = new StringSet();
        $command->setArguments(['foo', 'bar']);
        $clientName = 'default';

        $data = $this->createMock(CollectorData::class);
        $data->expects($this->once())
            ->method('getCommands')
            ->willReturn([$command]);
        $data->expects($this->once())
            ->method('getClientName')
            ->willReturn($clientName);

        $mock = $this->createMock(CollectorInterface::class);
        $mock->expects($this->once())
            ->method('getData')
            ->willReturn([$data]);

        $collector = new PredisCollector('redis', $mock);

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
