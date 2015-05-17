<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Utils\LocalFilesystem;

class ImageImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImportAndProcessImages()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $images = ['test_image.jpg', 'test_image2.jpg'];

        $queue = $factory->getEventQueue();
        foreach ($images as $image) {
            $queue->add(new ImageImportDomainEvent($image));
        }

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = count($images);
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
