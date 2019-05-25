<?php

namespace Anper\PredisCollector\Processor;

use Predis\Command\Processor\ProcessorChain;
use Predis\Profile\RedisProfile;
use Predis\ClientInterface;

class ProcessorProvider implements ProviderInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $prototype;

    /**
     * @param ProcessorInterface|null $prototype
     */
    public function __construct(ProcessorInterface $prototype = null)
    {
        $this->prototype = $prototype ?? new TraceableProcessor();
    }

    /**
     * @param ClientInterface $client
     * @return ProcessorInterface
     *
     * @throws UnexpectedProfileException
     */
    public function register(ClientInterface $client): ProcessorInterface
    {
        $profile = $this->getProfile($client);
        $chain = $this->getProcessorChain($profile);

        $processor = clone $this->prototype;

        $chain->add($processor);

        return $processor;
    }

    /**
     * @param ClientInterface $client
     * @return RedisProfile
     *
     * @throws UnexpectedProfileException
     */
    protected function getProfile(ClientInterface $client): RedisProfile
    {
        $profile = $client->getProfile();

        if ($profile instanceof RedisProfile) {
            return $profile;
        }

        $type = \is_object($profile)
            ? \get_class($profile)
            : \gettype($profile);

        throw new UnexpectedProfileException(
            sprintf('Expected profile instance of "%s", given "%s"', RedisProfile::class, $type)
        );
    }

    /**
     * @param RedisProfile $profile
     * @return ProcessorChain
     */
    protected function getProcessorChain(RedisProfile $profile): ProcessorChain
    {
        $processor = $profile->getProcessor();

        if ($processor instanceof ProcessorChain) {
            return $processor;
        }

        $chain = new ProcessorChain();

        if ($processor) {
            $chain->add($processor);
        }

        $profile->setProcessor($chain);

        return $chain;
    }
}
