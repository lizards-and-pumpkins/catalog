<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent
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
        $result = $this->domainEvent->getImageFileName();
        $this->assertEquals($this->dummyImageFileName, $result);
    }
}
