<?php

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 */
class TemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyTemplateId = 'foo';

    /**
     * @var mixed
     */
    private $stubProjectionSourceData = 'stub-projection-source-data';

    /**
     * @var TemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
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
