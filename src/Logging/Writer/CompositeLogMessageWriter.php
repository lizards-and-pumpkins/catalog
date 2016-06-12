<?php

namespace LizardsAndPumpkins\Logging\Writer;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;

class CompositeLogMessageWriter implements LogMessageWriter
{
    /**
     * @var LogMessageWriter[]
     */
    private $writers;

    public function __construct(LogMessageWriter ...$logMessageWriters)
    {
        $this->writers = $logMessageWriters;
    }

    public function write(LogMessage $logMessage)
    {
        every($this->writers, function (LogMessageWriter $writer) use ($logMessage) {
            $writer->write($logMessage);
        });
    }
}
