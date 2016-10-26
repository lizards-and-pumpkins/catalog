<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessageNameException;

class MessageName
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->initializeName(trim($name));
    }

    private function initializeName(string $name)
    {
        if ('' === $name) {
            throw new InvalidQueueMessageNameException('The message name must not be empty');
        }
        $this->name = $name;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
