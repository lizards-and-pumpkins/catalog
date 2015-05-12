<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImportImageDomainEvent
 */
class ImportImageDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnPassedImagePaths()
    {
        $imagePaths = [__DIR__ . '/../../../shared-fixture/test_image.jpg'];
        $event = new ImportImageDomainEvent($imagePaths);

        $this->assertEquals($imagePaths, $event->getImages());
    }
}
