<?php

namespace Brera;

use Brera\Log\InMemoryLogger;
use Brera\Log\LogMessage;

/**
 * @covers \Brera\InMemoryLogger
 */
class InMemoryLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testMessageIsStoredInMemory()
    {
        $stubLogMessage = $this->getMock(LogMessage::class);

        $logger = new InMemoryLogger();
        $logger->log($stubLogMessage);

        $messages = $logger->getMessages();

        $this->assertContains($stubLogMessage, $messages);
    }
}
