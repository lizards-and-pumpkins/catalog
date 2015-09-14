<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpsUrl;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent;
use LizardsAndPumpkins\Utils\LocalFilesystem;

class ImageImportTest extends AbstractIntegrationTest
{
    private function flushProcessedImagesDir()
    {
        $localFilesystem = new LocalFilesystem();
        $processedImagesDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;
        if (is_dir($processedImagesDir)) {
            $localFilesystem->removeDirectoryAndItsContent($processedImagesDir);
        }
        mkdir($processedImagesDir);
    }

    protected function setUp()
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }

        $this->flushProcessedImagesDir();
    }

    protected function tearDown()
    {
        $this->flushProcessedImagesDir();
    }

    public function testImagesAreImportedAndProcessed()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpsUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $images = ['../test_image.jpg', '../test_image2.jpg'];

        $queue = $factory->getEventQueue();
        foreach ($images as $image) {
            $queue->add(new ImageWasUpdatedDomainEvent($image));
        }

        $factory->createDomainEventConsumer()->process();

        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        foreach ($images as $image) {
            $filePath = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR . '/' . $image;
            $this->assertTrue(file_exists($filePath));

            $fileInfo = getimagesize($filePath);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_WIDTH, $fileInfo[0]);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_HEIGHT, $fileInfo[1]);
            $this->assertEquals('image/jpeg', $fileInfo['mime']);
        }
    }
}
