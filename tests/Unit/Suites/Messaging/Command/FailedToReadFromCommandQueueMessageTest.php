<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\FailedToReadFromCommandQueueMessage
 */
class FailedToReadFromCommandQueueMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailedToReadFromCommandQueueMessage
     */
    private $message;

    /**
     * @var \Exception
     */
    private $testException;

    protected function setUp()
    {
        $this->testException = new \Exception('foo');
        $this->message = new FailedToReadFromCommandQueueMessage($this->testException);
    }

    public function testLogMessageIsReturned()
    {
        $result = (string) $this->message;
        $expectation = "Failed to read from command queue message with following exception:\n\nfoo";

        $this->assertEquals($expectation, $result);

    }

    public function testExceptionContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis()
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertContains($this->testException->getFile(), $synopsis);
        $this->assertContains((string) $this->testException->getLine(), $synopsis);
    }
}
