<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DataVersion;
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

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataVersion;

    protected function setUp()
    {
        $this->dummyImageFilePath = 'test_image.jpg';
        $this->stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->domainEvent = new ImageWasAddedDomainEvent($this->dummyImageFilePath, $this->stubDataVersion);
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

    public function testItReturnsTheInjectedDataVersionInstance()
    {
        $this->assertSame($this->stubDataVersion, $this->domainEvent->getDataVersion());
    }
}
