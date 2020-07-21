<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

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

    public function getHandlerFor(Message $event) : DomainEventHandler
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

    private function getUnqualifiedDomainEventHandlerClassName(Message $event) : string
    {
        $camelCaseEventName = $this->snakeCaseToCamelCase($event->getName());
        return $camelCaseEventName . 'DomainEventHandler';
    }

    private function snakeCaseToCamelCase(string $name) : string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
