<?php


namespace LizardsAndPumpkins\Queue;

/**
 * @covers LizardsAndPumpkins\Queue\QueueAddLogMessage
 */
class QueueAddLogMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueue;

    protected function setUp()
    {
        $this->stubQueue = $this->getMock(Queue::class);
    }
    public function testItUsesTheClassNameForTheStringRepresentationForObjects()
    {
        $expected = sprintf('%s instance added to queue', __CLASS__);
        $this->assertSame($expected, (string) new QueueAddLogMessage($this, $this->stubQueue));
    }

    /**
     * @param array|int|float|string|resource|null $nonObject
     * @param string $expected
     * @dataProvider nonObjectDataProvider
     */
    public function testItUsesTheDataTypeForTheStringRepresentationForNonObjects($nonObject, $expected)
    {
        $this->assertSame($expected, (string) new QueueAddLogMessage($nonObject, $this->stubQueue));
    }

    /**
     * @return array[]
     */
    public function nonObjectDataProvider()
    {
        return [
            [[], 'Array added to queue'],
            [2, 'Integer added to queue'],
            [0.9, 'Double added to queue'],
            ['foo', 'String added to queue'],
            [fopen(__FILE__, 'r'), 'Resource added to queue'],
            [null, 'NULL added to queue']
        ];
    }

    public function testTheQueueIsPartOfTheMessageContext()
    {
        $logMessage = new QueueAddLogMessage(new \stdClass(), $this->stubQueue);
        $this->assertArrayHasKey('queue', $logMessage->getContext());
        $this->assertSame($this->stubQueue, $logMessage->getContext()['queue']);
    }

    public function testTheAddedDataIsPartOfTheMessageContext()
    {
        $logMessage = new QueueAddLogMessage($this, $this->stubQueue);
        $this->assertArrayHasKey('data', $logMessage->getContext());
        $this->assertSame($this, $logMessage->getContext()['data']);
    }

    public function testTheContextSynopsisIncludesTheQueueClassName()
    {
        $logMessage = new QueueAddLogMessage($this, $this->stubQueue);
        $this->assertContains(get_class($this->stubQueue), $logMessage->getContextSynopsis());
    }
}
