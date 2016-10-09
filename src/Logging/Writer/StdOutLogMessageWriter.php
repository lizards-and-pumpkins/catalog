<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging\Writer;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;

class StdOutLogMessageWriter implements LogMessageWriter
{
    public function write(LogMessage $logMessage)
    {
        echo get_class($logMessage) . ":\t" . $logMessage . "\n";
    }
}
