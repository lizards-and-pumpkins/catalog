<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEventHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CurrentDataVersionWasSetDomainEventHandlerTest extends TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    private function createHandler(string $targetDataVersion): CurrentDataVersionWasSetDomainEventHandler
    {
        $event = new CurrentDataVersionWasSetDomainEvent(DataVersion::fromVersionString($targetDataVersion));

        return new CurrentDataVersionWasSetDomainEventHandler(
            $event->toMessage(),
            $this->mockDataPoolWriter,
            $this->mockDataPoolReader
        );
    }

    protected function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
    }

    public function testIsADomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->createHandler('foo'));
    }

    public function testSetsTheDataVersionFromTheEventViaTheDataPoolWriter()
    {
        $targetVersion = 'bar';
        $this->mockDataPoolWriter->expects($this->once())->method('setCurrentDataVersion')->with($targetVersion);
        $this->createHandler($targetVersion)->process();
    }

    public function testSetsThePreviousDataVersionFromTheDataPoolReaderViaTheDataPoolWriter()
    {
        $previousVersion = 'qux';
        $this->mockDataPoolReader->method('getCurrentDataVersion')->willReturn($previousVersion);
        $this->mockDataPoolWriter->expects($this->once())->method('setPreviousDataVersion')
            ->with($previousVersion);
        $this->createHandler('foo')->process();
    }
}
