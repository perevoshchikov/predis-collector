<?php

namespace Anper\RedisCollector;

use Anper\RedisCollector\Format\CommandFormatterInterface;
use Anper\RedisCollector\Format\Response\DefaultFormatter as ResponseFormatter;
use Anper\RedisCollector\Format\Command\DefaultFormatter as CommandFormatter;
use Anper\RedisCollector\Format\ResponseFormatterInterface;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Class RedisCollector
 * @package Anper\RedisCollector
 */
class RedisCollector extends DataCollector implements Renderable, AssetProvider
{
    /**
     * @var ConnectionInterface[]
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $responseFormatters = [];

    /**
     * @var array
     */
    protected $commandFormatters = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @param ConnectionInterface|null $connection
     * @param string $name
     */
    public function __construct(ConnectionInterface $connection = null, string $name = 'redis')
    {
        if ($connection !== null) {
            $this->addConnection($connection);
        }

        $this->name = $name;

        $this->addResponseFormatter(new ResponseFormatter(), 0);
        $this->addCommandFormatter(new CommandFormatter(), 0);
    }

    /**
     * @param ConnectionInterface $connection
     * @return RedisCollector
     */
    public function addConnection(ConnectionInterface $connection): self
    {
        $id = spl_object_id($connection);

        $this->connections[$id] = $connection;

        return $this;
    }

    /**
     * @return ConnectionInterface[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param ResponseFormatterInterface $formatter
     * @param int $priority
     * @return RedisCollector
     */
    public function addResponseFormatter(ResponseFormatterInterface $formatter, int $priority = 10): self
    {
        $id = spl_object_id($formatter);

        $this->responseFormatters[$id] = [$formatter, $priority];

        return $this;
    }

    /**
     * @return array
     */
    public function getResponseFormatters(): array
    {
        return $this->responseFormatters;
    }

    /**
     * @param CommandFormatterInterface $formatter
     * @param int $priority
     * @return RedisCollector
     */
    public function addCommandFormatter(CommandFormatterInterface $formatter, int $priority = 10): self
    {
        $id = spl_object_id($formatter);

        $this->commandFormatters[$id] = [$formatter, $priority];

        return $this;
    }

    /**
     * @return array
     */
    public function getCommandFormatters(): array
    {
        return $this->commandFormatters;
    }

    /**
     * @inheritdoc
     */
    public function collect()
    {
        $data = [
            'nb_profiles' => 0,
            'duration' => 0,
            'memory' => 0,
            'profiles' => []
        ];

        $this->sortFormatters($this->responseFormatters);
        $this->sortFormatters($this->commandFormatters);

        foreach ($this->connections as $connection) {
            $profiles = $this->collectProfiles($connection);

            $data['nb_profiles'] += \count($profiles);

            foreach ($profiles as $profile) {
                $data['duration']  += $profile['duration'];
                $data['memory']    += $profile['memory'];
                $data['profiles'][] = $profile;
            }
        }

        $data['duration_str'] = $this->getDataFormatter()
            ->formatDuration($data['duration']);

        $data['memory_str'] = $this->getDataFormatter()
            ->formatBytes($data['memory']);

        return $data;
    }

    /**
     * @param ConnectionInterface $connection
     * @return array
     */
    protected function collectProfiles(ConnectionInterface $connection): array
    {
        $profiles = [];

        foreach ($connection->getProfiles() as $profile) {
            if ($profile instanceof Profile) {
                $profiles[] = [
                    'prepared_profile' => $this->formatCommand($profile),
                    'prepared_response' => $this->formatResponse($profile->getResponse()),
                    'duration' => $profile->getDuration(),
                    'duration_str' => $this->getDataFormatter()->formatDuration($profile->getDuration()),
                    'memory' => $profile->getMemoryUsage(),
                    'memory_str' => $this->getDataFormatter()->formatBytes((string) $profile->getMemoryUsage()),
                    'is_success' => $profile->isSuccess(),
                    'error_message' => (string) $profile->getError(),
                    'connection_id' => $connection->getConnectionId(),
                ];
            }
        }

        return $profiles;
    }

    /**
     * @inheritdoc
     */
    public function getWidgets()
    {
        return [
            $this->name => [
                'icon' => 'align-justify',
                'widget' => 'PhpDebugBar.Widgets.RedisQueriesWidget',
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
        return [
            'css' => __DIR__ . '/../resources/css/widget.css',
            'js' => __DIR__ . '/../resources/js/widget.js',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Profile $profile
     * @return string
     */
    protected function formatCommand(Profile $profile): string
    {
        foreach ($this->commandFormatters as $item) {
            /** @var CommandFormatterInterface $formatter */
            $formatter = $item[0];

            if ($formatter->supports($profile)) {
                return $formatter->format($profile);
            }
        }

        return '';
    }

    /**
     * @param mixed $response
     * @return string
     */
    protected function formatResponse($response): string
    {
        foreach ($this->responseFormatters as $item) {
            /** @var ResponseFormatterInterface $formatter */
            $formatter = $item[0];

            if ($formatter->supports($response)) {
                return $formatter->format($response);
            }
        }

        return '';
    }

    /**
     * @param array $formatters
     */
    protected function sortFormatters(array &$formatters): void
    {
        usort($formatters, function (array $a, array $b) {
            return $b[1] <=> $a[1];
        });
    }
}
