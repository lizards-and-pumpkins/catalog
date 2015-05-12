<?php

namespace Brera;

use Brera\Image\ImportImageDomainEvent;
use Brera\Utils\LocalFilesystem;

class ImageImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function importImageDomainEventShouldProduceImages()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $images = ['test_image.jpg', 'test_image2.jpg'];
        $event = new ImportImageDomainEvent($images);

        $queue = $factory->getEventQueue();
        $queue->add($event);

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $this->assertEmpty($factory->getLogger()->getMessages());

        foreach ($images as $image) {
            $filePath = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR . '/' . $image;
            $this->assertTrue(file_exists($filePath));

            $fileInfo = getimagesize($filePath);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_WIDTH, $fileInfo[0]);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_HEIGHT, $fileInfo[1]);
            $this->assertEquals('image/jpeg', $fileInfo['mime']);
        }
    }

    protected function setUp()
    {
        $this->flushProcessedImagesDir();
    }

    protected function tearDown()
    {
        $this->flushProcessedImagesDir();
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

    private function flushProcessedImagesDir()
    {
        $localFilesystem = new LocalFilesystem();
        $processedImagesDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;
        $localFilesystem->removeDirectoryAndItsContent($processedImagesDir);
        mkdir($processedImagesDir);
    }
}
