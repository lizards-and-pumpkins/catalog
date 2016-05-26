<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidMessageMetadataException;

class MessageMetadata
{
    /**
     * @var string[]
     */
    private $metadata;

    public function __construct(array $metadata)
    {
        $this->validateMetadataKeys($metadata);
        $this->metadata = $metadata;
    }

    private function validateMetadataKeys(array $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->validateKey($key);
            $this->validateValue($value);
        }
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    private function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidMessageMetadataException('The message metadata may only have string array keys');
        }
        if ('' === $key) {
            throw new InvalidMessageMetadataException('The message metadata array keys must not be empty');
        }
    }

    private function validateValue($value)
    {
        if (! is_string($value) && ! is_int($value) && ! is_bool($value) && ! is_double($value)) {
            throw new InvalidMessageMetadataException(sprintf(
                'The message metadata values may only me strings, booleans, integers or doubles, got %s',
                $this->getType($value)
            ));
        }
    }
    
    private function getType($var): string
    {
        return is_object($var) ?
            get_class($var) :
            gettype($var);
    }
}
