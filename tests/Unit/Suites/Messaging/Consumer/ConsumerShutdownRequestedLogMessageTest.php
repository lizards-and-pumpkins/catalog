<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Logging\LogMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ConsumerShutdownRequestedLogMessage
 */
class ConsumerShutdownRequestedLogMessageTest extends TestCase
{
    public function testIsALogMessage()
    {
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $this->assertInstanceOf(LogMessage::class, new ConsumerShutdownRequestedLogMessage(getmypid(), $dummyDirective));
    }

    public function testFormatsMessageWithPid()
    {
        $currentPid = 555;
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $expected = sprintf('Shutting down consumer PID %s', $currentPid);
        $this->assertEquals($expected, new ConsumerShutdownRequestedLogMessage($currentPid, $dummyDirective));
    }

    public function testReturnsContextWithCurrentPidAndDirective()
    {
        $currentPid = 555;
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $logMessage = new ConsumerShutdownRequestedLogMessage($currentPid, $dummyDirective);
        $expected = [
            'current_pid' => $currentPid,
            'shutdown_directive' => $dummyDirective,
        ];
        $this->assertSame($expected, $logMessage->getContext());
    }

    public function testReturnsContextSynopsisWithPidAndDirectivePattern()
    {
        $currentPid = 555;
        $stubDirective = $this->createMock(ShutdownWorkerDirective::class);
        $stubDirective->method('getPid')->willReturn('*');
        $logMessage = new ConsumerShutdownRequestedLogMessage($currentPid, $stubDirective);
        $expected = 'Shutdown Directive PID: *, Consumer Process PID: 555';
        $this->assertEquals($expected, $logMessage->getContextSynopsis());
    }
}
