<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpsUrl;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
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

        $basePath = dirname(dirname(__DIR__));
        $fixtureImageDirectory = $basePath . '/shared-fixture';
        $absoluteImagePath = $fixtureImageDirectory . '/test_image.jpg';
        $relativeImagePath = (new LocalFilesystem())
            ->getRelativePath(getcwd(), $fixtureImageDirectory . '/test_image2.jpg');
        
        $images = [$absoluteImagePath, $relativeImagePath];

        $queue = $factory->getEventQueue();
        foreach ($images as $imageFilePath) {
            $queue->add(new ImageWasAddedDomainEvent($imageFilePath, DataVersion::fromVersionString('-1')));
        }

        $factory->createDomainEventConsumer()->process();

        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        foreach ($images as $originalImageFilePath) {
            $imageName = basename($originalImageFilePath);
            $processedImageDirectory = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;
            $this->assertFileExists($processedImageDirectory . '/' . $imageName);

            $fileInfo = getimagesize($processedImageDirectory . '/' . $imageName);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_WIDTH, $fileInfo[0]);
            $this->assertEquals(IntegrationTestFactory::PROCESSED_IMAGE_HEIGHT, $fileInfo[1]);
            $this->assertEquals('image/jpeg', $fileInfo['mime']);
        }
    }
}
