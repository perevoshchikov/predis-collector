<?php

namespace Anper\PredisCollector\Tests;

use Anper\PredisCollector\Processor\ProcessorInterface;
use Anper\PredisCollector\Processor\ProcessorProvider;
use Anper\PredisCollector\Processor\UnexpectedProfileException;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Predis\Command\Processor\ProcessorChain;
use Predis\Profile\RedisProfile;

class ProcessorProviderTest extends TestCase
{
    public function testRegister()
    {
        $profile = new RedisProfileMock();

        $processor = $this->setProcessor($profile);

        $this->assertProcessors($profile, [$processor]);
    }

    public function testReplacePrevProcessor()
    {
        $profile = new RedisProfileMock();

        $processor1 = $this->createMock(ProcessorInterface::class);
        $profile->setProcessor($processor1);

        $processor2 = $this->setProcessor($profile);

        $this->assertProcessors($profile, [$processor1, $processor2]);
    }

    /**
     * @param RedisProfile $profile
     * @param array $processors
     */
    protected function assertProcessors(RedisProfile $profile, array $processors): void
    {
        /** @var ProcessorChain $chain */
        $chain = $profile->getProcessor();

        $this->assertInstanceOf(ProcessorChain::class, $chain);
        $this->assertEquals($processors, $chain->getProcessors());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function profileProvider(): array
    {
        return [
            [new \DateTime()],
            ['string'],
        ];
    }

    /**
     * @param $profile
     * @dataProvider profileProvider
     */
    public function testInvalidProfileType($profile)
    {
        $this->expectException(UnexpectedProfileException::class);

        $this->setProcessor($profile);
    }

    public function testProcessorPrototype()
    {
        $profile = new RedisProfileMock();

        $processor1 = $this->createMock(ProcessorInterfaceMock::class);

        $processor2 = $this->setProcessor($profile, $processor1);

        $this->assertEquals($processor1, $processor2);
    }

    /**
     * @param $profile
     * @param ProcessorInterface|null $processor
     *
     * @return ProcessorInterface
     */
    protected function setProcessor($profile, ProcessorInterface $processor = null): ProcessorInterface
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->atLeastOnce())
            ->method('getProfile')
            ->willReturn($profile);

        return (new ProcessorProvider($processor))->register($client);
    }
}
