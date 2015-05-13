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
    public function itShouldReturnPassedImageFilename()
    {
        $imageFilename = 'test_image.jpg';
        $event = new ImportImageDomainEvent($imageFilename);

        $this->assertEquals($imageFilename, $event->getImage());
    }
}
