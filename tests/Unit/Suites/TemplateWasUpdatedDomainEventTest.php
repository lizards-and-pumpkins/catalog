<?php

namespace Brera;

/**
 * @covers \Brera\TemplateWasUpdatedDomainEvent
 */
class TemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetSourceList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRootSnippetSourceList;

    /**
     * @var TemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $dummyRootSnippetId = 'foo';
        $this->stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $this->domainEvent = new TemplateWasUpdatedDomainEvent(
            $dummyRootSnippetId,
            $this->stubRootSnippetSourceList
        );
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testRootSnippetSourceListIsReturned()
    {
        $result = $this->domainEvent->getProjectionSourceData();
        $this->assertSame($this->stubRootSnippetSourceList, $result);
    }
}
