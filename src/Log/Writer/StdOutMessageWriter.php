<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class StdOutMessageWriter implements LogMessageWriter
{
    public function persist(LogMessage $logMessage)
    {
        echo get_class($logMessage) . ":\t" . $logMessage . "\n";
    }
}
