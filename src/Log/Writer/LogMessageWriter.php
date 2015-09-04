<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

interface LogMessageWriter
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function persist(LogMessage $logMessage);
}
