<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class StdOutLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        echo get_class($logMessage) . ":\t" . $logMessage . "\n";
    }
}
