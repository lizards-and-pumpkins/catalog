<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

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

    public function __construct(Message $message, ImageProcessorCollection $imageProcessorCollection)
    {
        $this->event = ImageWasAddedDomainEvent::fromMessage($message);
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process()
    {
        // todo: use $this->event->getDataVersion() and use it while processing...!
        $this->imageProcessorCollection->process($this->event->getImageFilePath());
    }
}
