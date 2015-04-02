<?php

namespace Brera;

use Brera\ImageImport\ImportImageDomainEvent;

class ImageImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function importImageDomainEventShouldProduceImages()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $images = [
            __DIR__ . '/../../shared-fixture/test_image.jpg',
            __DIR__ . '/../../shared-fixture/test_image2.jpg',
        ];
        $event = ImportImageDomainEvent::fromImages($images);

        $queue = $factory->getEventQueue();
        $queue->add($event);

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $this->assertEmpty($factory->getLogger()->getMessages());
    }

    /**
     * @return PoCMasterFactory
     */
    private function prepareIntegrationTestMasterFactory()
    {
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        $factory->register(new FrontendFactory());

        return $factory;
    }
}
