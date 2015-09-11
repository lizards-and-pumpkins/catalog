<?php

namespace Brera\Log;

/**
 * @covers \Brera\Log\InMemoryLogger
 */
class InMemoryLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryLogger
     */
    private $logger;

    protected function setUp()
    {
        $this->logger = new InMemoryLogger();
    }

    public function testItStoresTheMessagesInOrder()
    {
        $stubLogMessage1 = $this->getMock(LogMessage::class);
        $stubLogMessage2 = $this->getMock(LogMessage::class);
        $stubLogMessage3 = $this->getMock(LogMessage::class);
        
        $this->logger->log($stubLogMessage1);
        $this->logger->log($stubLogMessage2);
        $this->logger->log($stubLogMessage3);
        
        $this->assertSame([
            $stubLogMessage1,
            $stubLogMessage2,
            $stubLogMessage3
        ], $this->logger->getMessages());
    }


    public function testItOnlyKeepsA500MessagesRollingWindow()
    {
        $stubLogMessage1 = $this->getMock(LogMessage::class);
        $stubLogMessage2 = $this->getMock(LogMessage::class);
        $otherLogMessage = $this->getMock(LogMessage::class);

        $this->logger->log($stubLogMessage1);
        $this->logger->log($stubLogMessage2);

        for ($i = 0; $i < 498; $i++) {
            $this->logger->log($otherLogMessage);
        }
        $this->assertSame($stubLogMessage1, $this->logger->getMessages()[0]);
        $this->assertSame($stubLogMessage2, $this->logger->getMessages()[1]);
        
        $this->logger->log($otherLogMessage);
        $this->assertSame($stubLogMessage2, $this->logger->getMessages()[0]);
        $this->assertSame($otherLogMessage, $this->logger->getMessages()[1]);
    }
}
