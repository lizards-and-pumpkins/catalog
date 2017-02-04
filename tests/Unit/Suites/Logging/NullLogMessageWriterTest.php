<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\NullLogMessageWriter
 */
class NullLogMessageWriterTest extends TestCase
{
    /**
     * @var NullLogMessageWriter
     */
    private $writer;

    protected function setUp()
    {
        $this->writer = new NullLogMessageWriter();
    }
    
    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
    }

    public function testItTakesALogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $mockLogMessage */
        $mockLogMessage = $this->createMock(LogMessage::class);
        $mockLogMessage->expects($this->never())->method('__toString');
        $this->writer->write($mockLogMessage);
    }
}
