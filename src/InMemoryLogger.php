<?php

namespace Brera\PoC;

use Psr\Log\AbstractLogger;

class InMemoryLogger extends AbstractLogger
{
    /**
     * @var LogMessage[]
     */
    private $messages = [];

	/**
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
    public function log($level, $message, array $context = [])
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
