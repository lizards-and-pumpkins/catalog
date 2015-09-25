<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DomainEventHandler;

class ImageWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImageWasAddedDomainEvent
     */
    private $event;

    /**
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    public function __construct(ImageWasAddedDomainEvent $event, ImageProcessorCollection $imageProcessorCollection)
    {
        $this->event = $event;
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process()
    {
        // todo: use $this->event->getDataVersion() and use it while processing...!
        $this->imageProcessorCollection->process($this->event->getImageFilePath());
    }
}
