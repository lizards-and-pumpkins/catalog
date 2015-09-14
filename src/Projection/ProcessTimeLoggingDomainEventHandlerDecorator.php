<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\DomainEventHandler;
use LizardsAndPumpkins\Log\Logger;

class ProcessTimeLoggingDomainEventHandlerDecorator implements DomainEventHandler
{
    /**
     * @var DomainEventHandler
     */
    private $component;
    
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(DomainEventHandler $component, Logger $logger)
    {
        $this->component = $component;
        $this->logger = $logger;
    }
    
    public function process()
    {
        $start = microtime(true);
        $this->component->process();
        $this->logger->log(new DomainEventProcessedLogMessage(
            $this->formatMessageString(microtime(true) - $start),
            $this->component
        ));
    }

    /**
     * @param float $time
     * @return string
     */
    private function formatMessageString($time)
    {
        return sprintf('DomainEventHandler::process %s %f', get_class($this->component), $time);
    }
}
