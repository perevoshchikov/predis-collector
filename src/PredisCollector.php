<?php

namespace Anper\PredisCollector;

use Anper\Predis\CommandCollector\Collector;
use Anper\Predis\CommandCollector\CollectorInterface;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Predis\ClientInterface;
use Predis\Command\CommandInterface;

/**
 * Class PredisCollector
 * @package Anper\PredisCollector
 */
class PredisCollector extends DataCollector implements Renderable, AssetProvider
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var CollectorInterface
     */
    protected $collector;

    /**
     * @param string $name
     * @param CollectorInterface|null $collector
     */
    public function __construct(string $name = 'predis', CollectorInterface $collector = null)
    {
        $this->name      = $name;
        $this->collector = $collector ?? new Collector();
    }

    /**
     * @param ClientInterface $client
     * @param string|null $name
     * @return PredisCollector
     */
    public function addClient(ClientInterface $client, string $name = null): self
    {
        $this->collector->addClient($client, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function collect()
    {
        $data = [
            'nb_profiles' => 0,
            'profiles' => []
        ];

        foreach ($this->collector->getData() as $value) {
            foreach ($value->getCommands() as $command) {
                if ($command instanceof CommandInterface) {
                    $data['nb_profiles']++;

                    $data['profiles'][] = [
                        'method' => $command->getId(),
                        'arguments' => $command->getArguments(),
                        'connection' => $value->getClientName(),
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getWidgets()
    {
        return [
            $this->name => [
                'icon' => 'align-justify',
                'widget' => 'PhpDebugBar.Widgets.PredisCommandsWidget',
                'map' => $this->name,
                'default' => '[]'
            ],
            $this->name.':badge' => [
                'map' => $this->name . '.nb_profiles',
                'default' => 'null'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAssets()
    {
        $path = \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

        return [
            'css' => $path . 'css' . DIRECTORY_SEPARATOR . 'widget.css',
            'js' => $path . 'js' . DIRECTORY_SEPARATOR . 'widget.js',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }
}
