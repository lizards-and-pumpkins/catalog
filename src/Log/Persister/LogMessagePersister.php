<?php


// todo: rename to Writer
namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

// todo: rename to LogMessageWriter
interface LogMessagePersister
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function persist(LogMessage $logMessage);
}
