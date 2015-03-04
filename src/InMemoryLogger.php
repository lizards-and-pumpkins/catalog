<?php

namespace Brera;

class InMemoryLogger implements Logger
{
    /**
     * @var LogMessage[]
     */
    private $messages = [];

    /**
     * @param LogMessage $message
     */
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
