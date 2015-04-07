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
     * @param ImageProcessCommandSequence $command
     * @param string $image
     */
    private function createImageBasedOn($command, $image)
    {
        $this->processor->setImage($image);
        $targetDirectory = $this->config->getTargetDirectory();
        $directoryBasedOnInstructions = $this->runInstructions($command->getInstructions());
        $filename = basename($image);
        $this->createTargetDirectoryIfNeeded("$targetDirectory/$directoryBasedOnInstructions");
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
            $madeChangesToDefineDirectory .= $method . implode(',', $parameters);
            call_user_func_array([$this->processor, $method], $parameters);
        }
        // TODO md5 is a bad idea because no one knows then how the directory name is, but it shortens the dir
        // TODO and distributes the images based on their processing
        $directory = md5($madeChangesToDefineDirectory);

        return $directory;
    }

    /**
     * @param string $directory
     */
    private function createTargetDirectoryIfNeeded($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}



