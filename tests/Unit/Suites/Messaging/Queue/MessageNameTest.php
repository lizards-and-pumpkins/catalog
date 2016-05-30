<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessageNameException;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class MessageNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $emptyMessageName
     * @dataProvider emptyMessageNameProvider
     */
    public function testThrowsExceptionIfEmpty($emptyMessageName)
    {
        $this->expectException(InvalidQueueMessageNameException::class);
        $this->expectExceptionMessage('The message name must not be empty');
        new MessageName($emptyMessageName);
    }

    /**
     * @return array[]
     */
    public function emptyMessageNameProvider()
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testCanBeCastToString()
    {
        $name = 'foo-bar';
        $this->assertSame($name, (string) new MessageName($name));
    }
}
