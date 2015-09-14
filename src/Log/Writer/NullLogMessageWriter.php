<?php


namespace LizardsAndPumpkins\Log\Writer;

use LizardsAndPumpkins\Log\LogMessage;

class NullLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        // Do nothing
    }
}
