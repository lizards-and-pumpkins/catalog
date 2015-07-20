<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageWasUpdatedDomainEvent
 */
class ImageWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testPassedImageFilenameIsReturned()
    {
        $imageFilename = 'test_image.jpg';
        $event = new ImageWasUpdatedDomainEvent($imageFilename);

        $this->assertEquals($imageFilename, $event->getImage());
    }
}
