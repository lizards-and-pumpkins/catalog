<?php

namespace Brera;

/**
 * @covers \Brera\TemplateWasUpdatedDomainEvent
 */
class TemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyTemplateId = 'foo';

    /**
     * @var ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProjectionSourceData;

    /**
     * @var TemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $this->domainEvent = new TemplateWasUpdatedDomainEvent($this->dummyTemplateId, $this->stubProjectionSourceData);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProjectionSourceDataIsReturned()
    {
        $result = $this->domainEvent->getProjectionSourceData();
        $this->assertSame($this->stubProjectionSourceData, $result);
    }

    public function testTemplateIdIsReturned()
    {
        $result = $this->domainEvent->getTemplateId();
        $this->assertSame($this->dummyTemplateId, $result);
    }
}
