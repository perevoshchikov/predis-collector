<?php

namespace Anper\RedisCollector;

/**
 * Class Statement
 * @package Anper\RedisCollector
 */
class Statement
{
    /**
     * @var int
     */
    protected $startTime = 0;

    /**
     * @var int
     */
    protected $endTime = 0;

    /**
     * @var int
     */
    protected $duration = 0;

    /**
     * @var int
     */
    protected $startMemory = 0;

    /**
     * @var int
     */
    protected $endMemory = 0;

    /**
     * @var int
     */
    protected $memoryDelta = 0;

    /**
     * @var \Exception|null
     */
    protected $exception;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $response;

    /**
     * @param string $method
     * @param array $arguments
     */
    public function __construct(string $method, array $arguments = [])
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * @param float|null $startTime
     * @param int|null $startMemory
     */
    public function start(float $startTime = null, int $startMemory = null): void
    {
        $this->startTime   = $startTime ?? microtime(true);
        $this->startMemory = $startMemory ?? memory_get_usage(false);
    }

    /**
     * @param \Exception|null $exception
     * @param float|null $endTime
     * @param int|null $endMemory
     */
    public function end(\Exception $exception = null, float $endTime = null, int $endMemory = null): void
    {
        $this->endTime     = $endTime ?? microtime(true);
        $this->duration    = $this->endTime - $this->startTime;
        $this->endMemory   = $endMemory ?? memory_get_usage(false);
        $this->memoryDelta = $this->endMemory - $this->startMemory;
        $this->exception   = $exception;
    }

    /**
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @return float
     */
    public function getEndTime(): float
    {
        return $this->endTime;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getStartMemory(): int
    {
        return $this->startMemory;
    }

    /**
     * @return int
     */
    public function getEndMemory(): int
    {
        return $this->endMemory;
    }

    /**
     * @return int
     */
    public function getMemoryUsage(): int
    {
        return $this->memoryDelta;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->exception === null;
    }

    /**
     * @return \Exception|null
     */
    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->exception !== null ? $this->exception->getMessage() : '';
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        $params = [];

        foreach ($this->arguments as $name => $param) {
            if (mb_check_encoding($param, 'UTF-8')) {
                $params[$name] = (string) $param;
            } else {
                $param[$name] = '[BINARY DATA]';
            }
        }

        return $params;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }
}
