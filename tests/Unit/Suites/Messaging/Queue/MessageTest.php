<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTheInjectedTimestamp()
    {
        $date = '2016-05-18 06:00:00';
        $message = Message::withGivenTime('foo name', 'bar payload', [], new \DateTimeImmutable($date));
        $this->assertSame((new \DateTimeImmutable($date))->getTimestamp(), $message->getTimestamp());
    }

    public function testReturnsTheMessageName()
    {
        $name = 'foo';
        $message = Message::withCurrentTime($name, 'baz payload', []);
        $this->assertSame($name, $message->getName());
    }

    public function testValidatesTheMessageName()
    {
        $this->expectException(\InvalidArgumentException::class);
        Message::withCurrentTime('', 'qux payload', ['metadata']);
    }

    public function testReturnsThePayload()
    {
        $payload = 'bar';
        $message = Message::withCurrentTime('foo name', $payload, []);
        $this->assertSame($payload, $message->getPayload());
    }

    public function testReturnsTheMetadata()
    {
        $metadata = ['data_version' => 'foo-bar'];
        $message = Message::withCurrentTime('foo name', 'bar payload', $metadata);
        $this->assertSame($metadata, $message->getMetadata());
    }

    public function testItValidatesTheMetadata()
    {
        $this->expectException(\InvalidArgumentException::class);
        Message::withCurrentTime('foo name', 'bar payload', ['' => $this]);
    }

    public function testCanBeInstantiatedWithoutInjectingTheCurrentDateTime()
    {
        $startTime = time();
        $message = Message::withCurrentTime('some.name', 'some_payload', ['some' => 'metadata']);
        $this->assertInstanceOf(Message::class, $message);
        $isCurrentTime = $message->getTimestamp() === $startTime || $message->getTimestamp() === $startTime + 1;
        $this->assertTrue($isCurrentTime, 'The message was not instantiated for the current datetime');
    }

    public function testCanBeInstantiatedWithGivenTime()
    {
        $time = new \DateTimeImmutable('2016-05-18 06:00:00');
        $message = Message::withGivenTime('some.name', 'some_payload', ['some' => 'metadata'], $time);
        $this->assertSame($time->getTimestamp(), $message->getTimestamp());
    }

    public function testItCanBeJsonSerializedAndRehydrated()
    {
        $source = Message::withCurrentTime('foo', 'bar', ['baz' => 'qux']);
        $rehydrated = Message::rehydrate($source->serialize());

        $this->assertInstanceOf(Message::class, $rehydrated);
        $this->assertSame($source->getName(), $rehydrated->getName());
        $this->assertSame($source->getPayload(), $rehydrated->getPayload());
        $this->assertSame($source->getMetadata(), $rehydrated->getMetadata());
        $this->assertSame($source->getTimestamp(), $rehydrated->getTimestamp());
    }
}
