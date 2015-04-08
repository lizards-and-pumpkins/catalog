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
        $event = ImportImageDomainEvent::fromArray($images);

        $queue = $factory->getEventQueue();
        $queue->add($event);

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $this->assertEmpty($factory->getLogger()->getMessages());
    }

    protected function tearDown()
    {
        // TODO value is from commonfactory
        $dir = sys_get_temp_dir() . '/brera';
        $this->recursiveRemoveDirectory($dir);
        mkdir($dir);
    }

    /**
     * @param string $dir
     */
    protected function recursiveRemoveDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->recursiveRemoveDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
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
