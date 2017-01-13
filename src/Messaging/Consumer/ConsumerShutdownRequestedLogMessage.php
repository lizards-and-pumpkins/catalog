<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Logging\LogMessage;

class ConsumerShutdownRequestedLogMessage implements LogMessage
{
    /**
     * @var int
     */
    private $currentPid;

    /**
     * @var ShutdownWorkerDirective
     */
    private $directive;

    public function __construct(int $currentPid, ShutdownWorkerDirective $directive)
    {
        $this->currentPid = $currentPid;
        $this->directive = $directive;
    }

    public function __toString() : string
    {
        return sprintf('Shutting down consumer PID %s', $this->currentPid);
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return [
            'current_pid' => $this->currentPid,
            'shutdown_directive' => $this->directive,
        ];
    }

    public function getContextSynopsis() : string
    {
        $format = 'Shutdown Directive PID: %s, Consumer Process PID: %s';

        return sprintf($format, $this->directive->getPid(), $this->currentPid);
    }
}
