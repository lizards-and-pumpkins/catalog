<?php


namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 */
class CatalogWasImportedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataVersion;

    protected function setUp()
    {
        $this->stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->event = new CatalogWasImportedDomainEvent($this->stubDataVersion);
    }

    public function testItIsADomainEvent()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->event);
    }

    public function testItReturnsTheInjectedVersion()
    {
        $this->assertSame($this->stubDataVersion, $this->event->getDataVersion());
    }
}
