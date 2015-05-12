<?php

namespace Brera\Image;

use Brera\DomainEventHandler;

class ImportImageDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImportImageDomainEvent
     */
    private $event;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(ImportImageDomainEvent $event, ImageProcessor $imageProcessor)
    {
        $this->event = $event;
        $this->imageProcessor = $imageProcessor;
    }

    public function process()
    {
        array_map([$this->imageProcessor, 'process'], $this->event->getImages());
    }
}
