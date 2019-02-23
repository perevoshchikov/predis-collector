<?php

namespace Anper\PredisCollector\Format\Command;

use Anper\PredisCollector\Format\CommandFormatterInterface;
use Anper\PredisCollector\Profile;

/**
 * Class HighlightFormatter
 * @package Anper\PredisCollector\Format\Command
 */
class HighlightFormatter implements CommandFormatterInterface
{
    /**
     * @var array
     */
    protected $styles = [
        'method' => 'font-weight: bold; color: #333;',
        'arguments' => [
            'color: #d14;',
        ],
    ];

    /**
     * @return array
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * @param array $styles
     */
    public function setStyles(array $styles): void
    {
        $this->styles = $styles;
    }

    /**
     * @inheritDoc
     */
    public function supports(Profile $profile): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function format(Profile $profile): string
    {
        $result = $this->formatMethod($profile->getMethod());

        foreach ($profile->getArguments() as $key => $argument) {
            $result .= ' ' . $this->formatArgument($key, $argument);
        }

        return $result;
    }

    /**
     * @param string $method
     * @return string
     */
    protected function formatMethod(string $method): string
    {
        return sprintf('<span style="%s">%s</span>', $this->styles['method'] ?? '', $method);
    }

    /**
     * @param int $key
     * @param string $argument
     * @return string
     */
    protected function formatArgument(int $key, string $argument): string
    {
        return sprintf(
            '<span style="%s">%s</span>',
            $this->styles['arguments'][$key] ?? '',
            $argument === '' ? '""' : $argument
        );
    }
}
