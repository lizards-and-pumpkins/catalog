<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ImageWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $event;

    /**
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    public function __construct(Message $event, ImageProcessorCollection $imageProcessorCollection)
    {
        if ($event->getName() !== 'image_was_added_domain_event') {
            $message = sprintf('Expected "image_was_added" domain event, got "%s"', $event->getName());
            throw new NoImageWasAddedDomainEventMessageException($message);
        }
        $this->event = $event;
        $this->imageProcessorCollection = $imageProcessorCollection;
    }

    public function process()
    {
        // todo: use $this->event->getMetadata()['data_version'] and use it while processing...!
        $this->imageProcessorCollection->process(json_decode($this->event->getPayload(), true)['file_path']);
    }
}
