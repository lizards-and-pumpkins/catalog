<?php


namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

interface LogMessagePersister
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function persist(LogMessage $logMessage);
}
