<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

class Message
{
    private static $nameField = 'name';
    
    private static $payloadField = 'payload';
    
    private static $metadataField = 'metadata';
    
    private static $timestampField = 'timestamp';
    
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var string[]
     */
    private $metadata;

    private function __construct(string $name, string $payload, array $metadata, \DateTimeInterface $now)
    {
        $this->timestamp = $now->getTimestamp();
        $this->name = (string) new MessageName($name);
        $this->payload = $payload;
        $this->metadata = (new MessageMetadata($metadata))->getMetadata();
    }

    public static function withCurrentTime(string $name, string $payload, array $metadata): Message
    {
        return new self($name, $payload, $metadata, new \DateTimeImmutable());
    }

    public static function withGivenTime(string $name, string $payload, array $metadata, \DateTimeInterface $dateTime)
    {
        return new self($name, $payload, $metadata, $dateTime);
    }

    public function serialize(): string
    {
        return json_encode([
            self::$nameField => $this->getName(),
            self::$payloadField => $this->getPayload(),
            self::$metadataField => $this->getMetadata(),
            self::$timestampField => $this->getTimestamp(),
        ]);
    }

    public static function rehydrate(string $json): Message
    {
        $data = json_decode($json, true);
        return new Message(
            $data[self::$nameField],
            $data[self::$payloadField],
            $data[self::$metadataField],
            new \DateTimeImmutable('@' . $data[self::$timestampField])
        );
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
