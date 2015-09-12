<?php


namespace Brera\Projection;

use Brera\DomainEventHandler;

/**
 * @covers \Brera\Projection\DomainEventProcessedLogMessage
 */
class DomainEventProcessedLogMessageTest extends \PHPUnit_Framework_TestCase
{
    private $testMessage = 'Test message';
    
    /**
     * @var DomainEventProcessedLogMessage
     */
    private $logMessage;

    /**
     * @var DomainEventHandler
     */
    private $stubDomainEventHandler;

    protected function setUp()
    {
        $this->stubDomainEventHandler = $this->getMock(DomainEventHandler::class);
        $this->logMessage = new DomainEventProcessedLogMessage($this->testMessage, $this->stubDomainEventHandler);
    }

    public function testItReturnsTheGivenString()
    {
        $this->assertSame($this->testMessage, (string) $this->logMessage);
    }

    public function testTheDomainEventHandlerIsPartOfTheContext()
    {
        $this->assertInternalType('array', $this->logMessage->getContext());
        $this->assertArrayHasKey('domain_event_handler', $this->logMessage->getContext());
        $this->assertSame($this->stubDomainEventHandler, $this->logMessage->getContext()['domain_event_handler']);
    }
}
