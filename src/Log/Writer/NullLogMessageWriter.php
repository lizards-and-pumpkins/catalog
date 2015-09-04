<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class NullLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        // Do nothing
    }
}
