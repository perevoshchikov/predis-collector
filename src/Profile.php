<?php

namespace Anper\PredisCollector;

/**
 * Class Profile
 * @package Anper\PredisCollector
 */
class Profile
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
     * @var string|null
     */
    protected $error;

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
     * @param float|null $endTime
     * @param int|null $endMemory
     */
    public function end(float $endTime = null, int $endMemory = null): void
    {
        $this->endTime     = $endTime ?? microtime(true);
        $this->duration    = $this->endTime - $this->startTime;
        $this->endMemory   = $endMemory ?? memory_get_usage(false);
        $this->memoryDelta = $this->endMemory - $this->startMemory;
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
        return $this->error === null;
    }

    /**
     * @param string|null $error
     */
    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getError(): ?string
    {
        return $this->error;
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
                $params[$name] = '[BINARY DATA]';
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
