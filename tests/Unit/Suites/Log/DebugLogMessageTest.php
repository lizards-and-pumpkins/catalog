<?php

namespace LizardsAndPumpkins\Log;

/**
 * @covers \LizardsAndPumpkins\Log\DebugLogMessage
 */
class DebugLogMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testItIsALogMessage()
    {
        $this->assertInstanceOf(LogMessage::class, new DebugLogMessage('Test Message'));
    }

    public function testItReturnsTheLogMessage()
    {
        $this->assertSame('Test Message', (string) new DebugLogMessage('Test Message'));
    }

    public function testItReturnsTheGivenContext()
    {
        $context = ['foo' => 'bar'];
        $this->assertSame($context, (new DebugLogMessage('Foo', $context))->getContext());
    }

    public function testItReturnsTheContextInStringFormat()
    {
        $context = [
            'a string' => 'foo',
            'an int' => 123,
            'nothing' => null,
            'an object' => $this,
        ];
        $expected = preg_replace('#\s{2,}#', ' ', str_replace("\n", ' ', print_r([
            'a string' => 'foo',
            'an int' => 123,
            'nothing' => null,
            'an object' => get_class($this),
        ], true)));
        $this->assertSame($expected, (new DebugLogMessage('Test Message', $context))->getContextSynopsis());
    }
}
