<?php

namespace LizardsAndPumpkins\Logging;

interface LogMessageWriter
{
    /**
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage);
}
