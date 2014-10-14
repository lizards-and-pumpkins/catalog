<?php


namespace Brera\PoC;


class InMemoryLogger implements Logger
{
    /**
     * @var LogMessage[]
     */
    private $messages = [];

    /**
     * @param LogMessage $message
     * @return null
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