<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessagePayloadException;

class MessagePayload
{
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * @param mixed[] $payload
     */
    public function __construct(array $payload)
    {
        $this->validatePayload($payload, '');

        $this->payload = $payload;
    }

    /**
     * @return mixed[]
     */
    public function getPayload() : array
    {
        return $this->payload;
    }

    /**
     * @param mixed[] $payload
     * @param string $path
     */
    private function validatePayload(array $payload, string $path)
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $this->validatePayload($value, $path . '/' . $key);
            } elseif (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
                $message = sprintf(
                    'Invalid message payload data type found at "%s": %s (must be string, int, float or boolean)',
                    $path . '/' . $key,
                    $this->getType($value)
                );
                throw new InvalidQueueMessagePayloadException($message);
            }
        }
    }

    /**
     * @param mixed $var
     * @return string
     */
    private function getType($var) : string
    {
        return is_object($var) ?
            get_class($var) :
            gettype($var);
    }
}
