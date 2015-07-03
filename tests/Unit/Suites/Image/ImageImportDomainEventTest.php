<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageImportDomainEvent
 */
class ImageImportDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testPassedImageFilenameIsReturned()
    {
        $imageFilename = 'test_image.jpg';
        $event = new ImageImportDomainEvent($imageFilename);

        $this->assertEquals($imageFilename, $event->getImage());
    }
}
