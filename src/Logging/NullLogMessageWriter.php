<?php

namespace LizardsAndPumpkins\Logging;

class NullLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        // Do nothing
    }
}
