<?php

namespace Anper\PredisCollector;

use Anper\PredisCollector\Processor\ProcessorInterface;
use Anper\PredisCollector\Processor\ProcessorProvider;
use Anper\PredisCollector\Processor\ProviderInterface;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Predis\Command\CommandInterface;
use Predis\ClientInterface;

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
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var ProcessorInterface[]
     */
    protected $data = [];

    /**
     * @param string $name
     * @param ProviderInterface|null $provider
     */
    public function __construct(string $name = 'predis', ProviderInterface $provider = null)
    {
        $this->name     = $name;
        $this->provider = $provider ?? new ProcessorProvider();
    }

    /**
     * @param ClientInterface $client
     * @param string|null $name
     *
     * @return PredisCollector
     */
    public function addClient(ClientInterface $client, string $name = null): self
    {
        $processor = $this->provider->register($client);

        if (empty($name) && ($clientName = (string) $client->getConnection())) {
            $name = $clientName;
        }

        $this->data[$name ?? time()] = $processor;

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

        foreach ($this->data as $name => $processor) {
            foreach ($processor->getCommands() as $command) {
                if ($command instanceof CommandInterface) {
                    $data['nb_profiles']++;

                    $data['profiles'][] = [
                        'method' => $command->getId(),
                        'arguments' => $command->getArguments(),
                        'connection' => $name,
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
