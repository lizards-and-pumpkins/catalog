<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Queue\LoggingQueueDecorator;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\Clearable;

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
