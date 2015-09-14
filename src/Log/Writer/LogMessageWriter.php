<?php


namespace LizardsAndPumpkins\Log\Writer;

use LizardsAndPumpkins\Log\LogMessage;

interface LogMessageWriter
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage);
}
