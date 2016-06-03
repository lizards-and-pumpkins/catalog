<?php

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

    /**
     * @param string $name
     * @param string[]|int[]|float[]|bool[]|array[] $payload
     * @param string[] $metadata
     * @param \DateTimeInterface $now
     */
    private function __construct($name, array $payload, array $metadata, \DateTimeInterface $now)
    {
        $this->timestamp = $now->getTimestamp();
        $this->name = (string) new MessageName($name);
        $this->payload = (new MessagePayload($payload))->getPayload();
        $this->metadata = (new MessageMetadata($metadata))->getMetadata();
    }

    /**
     * @param string $name
     * @param string $payload
     * @param string[] $metadata
     * @return Message
     */
    public static function withCurrentTime($name, $payload, array $metadata)
    {
        return new self($name, $payload, $metadata, new \DateTimeImmutable());
    }

    /**
     * @param string $name
     * @param string $payload
     * @param string[] $metadata
     * @param \DateTimeInterface $dateTime
     * @return Message
     */
    public static function withGivenTime($name, $payload, array $metadata, \DateTimeInterface $dateTime)
    {
        return new self($name, $payload, $metadata, $dateTime);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode([
            self::$nameField => $this->getName(),
            self::$payloadField => $this->getPayload(),
            self::$metadataField => $this->getMetadata(),
            self::$timestampField => $this->getTimestamp(),
        ]);
    }

    /**
     * @param string $json
     * @return Message
     */
    public static function rehydrate($json)
    {
        $data = json_decode($json, true);
        return new Message(
            $data[self::$nameField],
            $data[self::$payloadField],
            $data[self::$metadataField],
            new \DateTimeImmutable('@' . $data[self::$timestampField])
        );
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string[]
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
