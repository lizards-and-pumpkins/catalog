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
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    public function __construct(ImageImportDomainEvent $event, ImageProcessorCollection $imageProcessorCollection)
    {
        $this->event = $event;
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process()
    {
        $this->imageProcessorCollection->process($this->event->getImage());
    }
}
