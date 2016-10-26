<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

class NullLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        // Do nothing
    }
}
