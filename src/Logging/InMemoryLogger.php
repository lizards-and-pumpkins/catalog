<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

class InMemoryLogger implements Logger
{
    private $maxMessagesToKeep = 500;
    
    /**
     * @var LogMessage[]
     */
    private $messages = [];

    public function log(LogMessage $message)
    {
        if (count($this->messages) === $this->maxMessagesToKeep) {
            array_shift($this->messages);
        }
        $this->messages[] = $message;
    }

    /**
     * @return LogMessage[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }
}
