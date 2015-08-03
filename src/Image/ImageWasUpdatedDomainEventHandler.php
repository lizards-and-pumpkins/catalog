<?php

namespace Brera\Image;

use Brera\DomainEventHandler;

class ImageWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImageWasUpdatedDomainEvent
     */
    private $event;

    /**
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    public function __construct(ImageWasUpdatedDomainEvent $event, ImageProcessorCollection $imageProcessorCollection)
    {
        $this->event = $event;
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process()
    {
        $this->imageProcessorCollection->process($this->event->getImageFileName());
    }
}
