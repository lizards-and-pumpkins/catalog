<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class DomainEventHandlerLocator
{
    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function getHandlerFor(Message $event)
    {
        $eventHandlerClass = $this->getUnqualifiedDomainEventHandlerClassName($event);
        $method = 'create' . $eventHandlerClass;

        if (!method_exists(DomainEventHandlerFactory::class, $method)) {
            throw new UnableToFindDomainEventHandlerException(
                sprintf('Unable to find a handler "%s" for event "%s"', $eventHandlerClass, $event->getName())
            );
        }

        return $this->factory->{$method}($event);
    }

    /**
     * @param Message $event
     * @return string
     */
    private function getUnqualifiedDomainEventHandlerClassName(Message $event)
    {
        $camelCaseEventName = $this->snakeCaseToCamelCase($event->getName());
        return $camelCaseEventName . 'Handler';
    }

    /**
     * @param string $name
     * @return string
     */
    private function snakeCaseToCamelCase($name)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
