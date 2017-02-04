<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessageNameException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class MessageNameTest extends TestCase
{
    /**
     * @dataProvider emptyMessageNameProvider
     */
    public function testThrowsExceptionIfEmpty(string $emptyMessageName)
    {
        $this->expectException(InvalidQueueMessageNameException::class);
        $this->expectExceptionMessage('The message name must not be empty');
        new MessageName($emptyMessageName);
    }

    /**
     * @return array[]
     */
    public function emptyMessageNameProvider() : array
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
