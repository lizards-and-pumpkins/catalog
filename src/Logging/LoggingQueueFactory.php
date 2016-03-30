<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Storage\Clearable;

class LoggingQueueFactory implements Factory
{
    use FactoryTrait;

    /**
     * @var Factory
     */
    private $implementationFactoryDelegate;

    public function __construct(Factory $implementationFactoryDelegate)
    {
        $this->implementationFactoryDelegate = $implementationFactoryDelegate;
    }

    /**
     * @return Queue|Clearable
     */
    public function createEventQueue()
    {
        return new LoggingQueueDecorator(
            $this->implementationFactoryDelegate->createEventQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return Queue|Clearable
     */
    public function createCommandQueue()
    {
        return new LoggingQueueDecorator(
            $this->implementationFactoryDelegate->createCommandQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }
}
