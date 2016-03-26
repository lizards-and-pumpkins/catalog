<?php


namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Logging\LogMessage;

interface LogMessageWriter
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage);
}
