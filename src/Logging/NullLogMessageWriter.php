<?php


namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Logging\LogMessage;

class NullLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        // Do nothing
    }
}
