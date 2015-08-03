<?php

namespace Brera;

/**
 * @covers \Brera\PageTemplateWasUpdatedDomainEvent
 */
class PageTemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetSourceList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRootSnippetSourceList;

    /**
     * @var PageTemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $dummyRootSnippetId = 'foo';
        $this->stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $this->domainEvent = new PageTemplateWasUpdatedDomainEvent(
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
        $result = $this->domainEvent->getRootSnippetSourceList();
        $this->assertSame($this->stubRootSnippetSourceList, $result);
    }
}
