<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
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
     * @param DomainEvent $event
     * @return DomainEventHandler
     */
    public function getHandlerFor(DomainEvent $event)
    {
        $eventClass = $this->getUnqualifiedDomainEventClassName($event);
        $method = 'create' . $eventClass . 'Handler';

        if (!method_exists(DomainEventHandlerFactory::class, $method)) {
            throw new UnableToFindDomainEventHandlerException(
                sprintf('Unable to find a handler for %s domain event', $eventClass)
            );
        }

        return $this->factory->{$method}($event);
    }

    /**
     * @param DomainEvent $event
     * @return string
     */
    private function getUnqualifiedDomainEventClassName(DomainEvent $event)
    {
        $qualifiedClassName = get_class($event);
        $lastQualifierPosition = strrpos($qualifiedClassName, '\\');

        if (false === $lastQualifierPosition) {
            return $qualifiedClassName;
        }

        return substr($qualifiedClassName, $lastQualifierPosition + 1);
    }
}
