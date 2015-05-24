<?php

namespace Brera\Image;

class ImageProcessorCommandSequence implements ImageProcessorCommand
{
    /**
     * @var ImageProcessorCommand[]
     */
    private $commands = [];

    public function addCommand(ImageProcessorCommand $command)
    {
        $this->commands[] = $command;
    }

    /**
     * @param string $imageBinaryData
     * @return string
     */
    public function execute($imageBinaryData)
    {
        return array_reduce($this->commands, function ($carryImageBinaryData, ImageProcessorCommand $command) {
            return $command->execute($carryImageBinaryData);
        }, $imageBinaryData);
    }
}
