<?php

namespace LizardsAndPumpkins\Log;

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
    public function getMessages()
    {
        return $this->messages;
    }
}
