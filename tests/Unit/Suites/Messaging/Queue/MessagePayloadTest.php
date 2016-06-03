<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessagePayloadException;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class MessagePayloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $testPayload
     * @dataProvider messagePayloadDataProvider
     */
    public function testItReturnsTheGivenPayload(array $testPayload)
    {
        $this->assertSame($testPayload, (new MessagePayload($testPayload))->getPayload());
    }

    /**
     * @return array[]
     */
    public function messagePayloadDataProvider()
    {
        return [
            [[]],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @param array[] $invalidPayload
     * @param string $expectedType
     * @param string $expectedPath
     * @dataProvider invalidPayloadProvider
     */
    public function testThrowsExceptionIfPayloadContainsNonScalarValues($invalidPayload, $expectedType, $expectedPath)
    {
        $this->expectException(InvalidQueueMessagePayloadException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid message payload data type found at "%s": %s (must be string, int, float or boolean)',
            $expectedPath,
            $expectedType
        ));
        
        new MessagePayload($invalidPayload);
    }

    /**
     * @return array[]
     */
    public function invalidPayloadProvider()
    {
        return [
            [['foo' => $this], get_class($this), '/foo'],
            [['bar' => fopen(__FILE__, 'r')], 'resource', '/bar'],
            [['baz' => null], 'NULL', '/baz'],
            [['sub' => ['qux' => null]], 'NULL', '/sub/qux'],
        ];
    }
}
