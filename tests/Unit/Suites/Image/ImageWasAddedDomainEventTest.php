<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Image\ImageWasAddedDomainEvent
 */
class ImageWasAddedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyImageFilePath;

    /**
     * @var ImageWasAddedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->dummyImageFilePath = 'test_image.jpg';
        $this->domainEvent = new ImageWasAddedDomainEvent($this->dummyImageFilePath);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testPassedImageFilenameIsReturned()
    {
        $result = $this->domainEvent->getImageFilePath();
        $this->assertEquals($this->dummyImageFilePath, $result);
    }
}
