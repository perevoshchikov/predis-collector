<?php

namespace Anper\RedisCollector\Adapter\Predis\Connection;

use Anper\RedisCollector\Format\FormatterInterface;
use Anper\RedisCollector\Statement;
use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Response\ErrorInterface;
use Predis\Response\Status;
use Anper\RedisCollector\ConnectionInterface as CollectorConnectionInterface;

/**
 * Class Adapter
 * @package Anper\RedisCollector\Adapter\Predis
 */
class Connection implements ConnectionInterface, FormatterInterface, CollectorConnectionInterface
{
    /**
     * @var Statement[]
     */
    protected $executedStatements = [];

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getSourceConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function getConnectionId(): string
    {
        return (string) $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function connect()
    {
        $this->connection->connect();
    }

    /**
     * @inheritDoc
     */
    public function disconnect()
    {
        $this->connection->disconnect();
    }

    /**
     * @inheritDoc
     */
    public function isConnected()
    {
        return $this->connection->isConnected();
    }

    /**
     * @inheritDoc
     */
    public function writeRequest(CommandInterface $command)
    {
        $this->connection->writeRequest($command);
    }

    /**
     * @inheritDoc
     */
    public function readResponse(CommandInterface $command)
    {
        return $this->connection->readResponse($command);
    }

    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws \Exception
     */
    public function executeCommand(CommandInterface $command)
    {
        return $this->profileCall($command);
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->connection->$name;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->connection->$name = $value;
    }

    /**
     * @param mixed $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        return \call_user_func_array([$this->connection, $name], $args);
    }

    /**
     * @inheritDoc
     */
    public function supports($response): bool
    {
        return \is_object($response) && $response instanceof Status;
    }

    /**
     * @inheritDoc
     */
    public function format($response): string
    {
        return (string) $response;
    }

    /**
     * @return Statement[]
     */
    public function getExecutedStatements(): array
    {
        return $this->executedStatements;
    }

    /**
     * @param Statement $stmt
     */
    public function addExecutedStatement(Statement $stmt): void
    {
        $this->executedStatements[] = $stmt;
    }

    /**
     * @param CommandInterface $command
     * @return Statement
     */
    protected function createStatement(CommandInterface $command): Statement
    {
        return new Statement($command->getId(), $command->getArguments());
    }

    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws \Exception
     */
    protected function profileCall(CommandInterface $command)
    {
        $trace = $this->createStatement($command);
        $trace->start();

        try {
            $result = $this->connection->executeCommand($command);
        } catch (\Exception $e) {
            $result = null;
        }

        if (\is_object($result) && $result instanceof ErrorInterface) {
            $trace->end(new \Exception($result->getMessage()));
        } else {
            $trace->end($e ?? null);
        }

        $this->addExecutedStatement($trace);

        if (isset($e)) {
            throw $e;
        }

        $trace->setResponse($result);

        return $result;
    }
}
