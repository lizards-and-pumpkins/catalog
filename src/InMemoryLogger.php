<?php

namespace Brera;

class InMemoryLogger implements Logger
{
    /**
     * @var LogMessage[]
     */
    private $messages = [];

    public function log(LogMessage $message)
    {
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
