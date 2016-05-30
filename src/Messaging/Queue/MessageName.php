<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessageNameException;

class MessageName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->initializeName(trim($name));
    }

    /**
     * @param string $name
     */
    private function initializeName($name)
    {
        if ('' === $name) {
            throw new InvalidQueueMessageNameException('The message name must not be empty');
        }
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
