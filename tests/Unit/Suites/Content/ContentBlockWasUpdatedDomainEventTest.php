<?php

namespace Brera\Content;

use Brera\DomainEvent;

/**
 * @covers \Brera\Content\ContentBlockWasUpdatedDomainEvent
 */
class ContentBlockWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockId;

    /**
     * @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var ContentBlockWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $this->domainEvent = new ContentBlockWasUpdatedDomainEvent(
            $this->stubContentBlockId,
            $this->stubContentBlockSource
        );
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testContentBlockSourceIsReturned()
    {
        $result = $this->domainEvent->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }
}
