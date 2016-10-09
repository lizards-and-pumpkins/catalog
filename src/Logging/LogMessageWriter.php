<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

interface LogMessageWriter
{
    public function write(LogMessage $logMessage);
}
