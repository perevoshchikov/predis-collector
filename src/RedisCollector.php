<?php

namespace Anper\RedisCollector;

use Anper\RedisCollector\Format\DefaultFormatter;
use Anper\RedisCollector\Format\FormatterInterface;
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

            if ($connection instanceof FormatterInterface) {
                $this->addResponseFormatter($connection);
            }
        }

        $this->name = $name;

        $this->addResponseFormatter(new DefaultFormatter(), 0);
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
     * @param FormatterInterface $formatter
     * @param int $priority
     * @return RedisCollector
     */
    public function addResponseFormatter(FormatterInterface $formatter, int $priority = 10): self
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
     * @inheritdoc
     */
    public function collect()
    {
        $data = [
            'nb_statements' => 0,
            'duration' => 0,
            'memory' => 0,
            'statements' => []
        ];

        $this->sortResponseFormatters();

        foreach ($this->connections as $connection) {
            $statements = $this->collectStatements($connection);

            $data['nb_statements'] += \count($statements);

            foreach ($statements as $statement) {
                $data['duration'] += $statement['duration'];
                $data['memory'] += $statement['memory'];
                $data['statements'][] = $statement;
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
    protected function collectStatements(ConnectionInterface $connection): array
    {
        $stmts = [];

        foreach ($connection->getExecutedStatements() as $stmt) {
            if ($stmt instanceof Statement) {
                $stmts[] = [
                    'prepared_stmt' => $this->formatCommand($stmt),
                    'prepared_response' => $this->formatResponse($stmt->getResponse()),
                    'duration' => $stmt->getDuration(),
                    'duration_str' => $this->getDataFormatter()->formatDuration($stmt->getDuration()),
                    'memory' => $stmt->getMemoryUsage(),
                    'memory_str' => $this->getDataFormatter()->formatBytes($stmt->getMemoryUsage()),
                    'is_success' => $stmt->isSuccess(),
                    'error_message' => $stmt->getErrorMessage(),
                    'connection_id' => $connection->getConnectionId(),
                ];
            }
        }

        return $stmts;
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
                'map' => 'redis',
                'default' => '[]'
            ],
            $this->name.':badge' => [
                'map' => 'redis.nb_statements',
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
     * @param Statement $statement
     * @return string
     */
    protected function formatCommand(Statement $statement): string
    {
        return $statement->getMethod() . ' ' . implode(' ', $statement->getArguments());
    }

    /**
     * @param mixed $response
     * @return string
     */
    protected function formatResponse($response): string
    {
        foreach ($this->responseFormatters as $item) {
            /** @var FormatterInterface $formatter */
            $formatter = $item[0];

            if ($formatter->supports($response)) {
                return $formatter->format($response);
            }
        }

        return '';
    }

    protected function sortResponseFormatters(): void
    {
        usort($this->responseFormatters, function (array $a, array $b) {
            return $b[1] <=> $a[1];
        });
    }
}
