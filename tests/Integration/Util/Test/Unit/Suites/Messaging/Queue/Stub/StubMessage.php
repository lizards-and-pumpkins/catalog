<?php

namespace LizardsAndPumpkins\Messaging\Queue\Stub;

class StubMessage implements \Serializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->value;
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->value = $serialized;
    }
}
