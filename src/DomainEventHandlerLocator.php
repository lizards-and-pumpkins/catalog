<?php

namespace Brera;

class DomainEventHandlerLocator
{
    /**
     * @var DomainEventFactory
     */
    private $factory;

    public function __construct(DomainEventFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param DomainEvent $event
     * @return DomainEventHandler
     * @throws UnableToFindDomainEventHandlerException
     */
    public function getHandlerFor(DomainEvent $event)
    {
        $eventClass = $this->getUnqualifiedDomainEventClassName($event);
        $method = 'create' . $eventClass . 'Handler';

        if (!method_exists($this->factory, $method)) {
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
