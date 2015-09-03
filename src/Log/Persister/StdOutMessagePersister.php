<?php


namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

class StdOutMessagePersister implements LogMessagePersister
{
    public function persist(LogMessage $logMessage)
    {
        echo get_class($logMessage) . "\t" . $logMessage . "\n";
    }
}
