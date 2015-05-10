<?php

namespace Brera\ImageProcessor;

class ImageProcessorCommandSequence
{
    /**
     * @var ImageProcessorCommand[]
     */
    private $commands = [];

    public function addCommand(ImageProcessorCommand $command)
    {
        array_push($this->commands,$command);
    }

    /**
     * @return ImageProcessorCommand[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
