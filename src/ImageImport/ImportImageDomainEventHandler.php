<?php

namespace Brera\ImageImport;

use Brera\DomainEventHandler;
use Brera\ImageProcessor\ImageProcessor;

class ImportImageDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ImageProcessConfiguration
     */
    private $config;

    /**
     * @var ImportImageDomainEvent
     */
    private $event;

    /**
     * @var ImageProcessor
     */
    private $processor;

    public function __construct(
        ImageProcessConfiguration $config,
        ImportImageDomainEvent $event,
        ImageProcessor $processor

    ) {
        $this->config = $config;
        $this->event = $event;
        $this->processor = $processor;
    }

    public function process()
    {
        foreach ($this->event->getImages() as $image) {
            foreach ($this->config as $instructions) {
                $this->createImageBasedOn($instructions, $image);
            }
        }
    }

    /**
     * @param mixed[] $instructions
     * @param string $image
     */
    private function createImageBasedOn($instructions, $image)
    {
        $this->processor->setImage($image);
        $targetDirectory = $this->config->getTargetDirectory();
        $directoryBasedOnInstructions = $this->runInstructions($instructions);
        $filename = basename($image);
        $this->processor->saveAsFile("$targetDirectory/$directoryBasedOnInstructions/$filename");
    }

    /**
     * @param mixed[] $instructions
     * @return string
     */
    private function runInstructions($instructions)
    {
        $madeChangesToDefineDirectory = '';
        foreach ($instructions as $method => $parameters) {
            $madeChangesToDefineDirectory .= $method;
            call_user_func_array([$this->processor, $method], $parameters);
        }
        $directory = md5($madeChangesToDefineDirectory);

        return $directory;
    }
}



