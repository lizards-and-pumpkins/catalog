<?php

namespace Brera\Image;

use Brera\DomainEventHandler;

class ImageImportDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImageImportDomainEvent
     */
    private $event;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(ImageImportDomainEvent $event, ImageProcessor $imageProcessor)
    {
        $this->event = $event;
        $this->imageProcessor = $imageProcessor;
    }

    public function process()
    {
        $this->imageProcessor->process($this->event->getImage());
    }
}
