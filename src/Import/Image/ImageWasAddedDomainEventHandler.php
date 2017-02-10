<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ImageWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    public function __construct(ImageProcessorCollection $imageProcessorCollection)
    {
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process(Message $message)
    {
        // todo: should we use $this->event->getDataVersion() and use it while processing...?
        $event = ImageWasAddedDomainEvent::fromMessage($message);
        $this->imageProcessorCollection->process($event->getImageFilePath());
    }
}
