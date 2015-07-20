<?php

namespace Brera\Image;

use Brera\DomainEvent;

/**
 * @covers \Brera\Image\ImageWasUpdatedDomainEvent
 */
class ImageWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyImageFileName;

    /**
     * @var ImageWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->dummyImageFileName = 'test_image.jpg';
        $this->domainEvent = new ImageWasUpdatedDomainEvent($this->dummyImageFileName);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testPassedImageFilenameIsReturned()
    {
        $result = $this->domainEvent->getImage();
        $this->assertEquals($this->dummyImageFileName, $this->domainEvent->getImage());
    }
}
