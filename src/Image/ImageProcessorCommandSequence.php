<?php

namespace Brera\Image;

class ImageProcessorCommandSequence
{
    /**
     * @var ImageProcessorCommand[]
     */
    private $commands = [];

    public function addCommand(ImageProcessorCommand $command)
    {
        array_push($this->commands, $command);
    }

    /**
     * @param string $imageStream
     * @return string
     */
    public function process($imageStream)
    {
        return array_reduce($this->commands, function ($carryImageStream, ImageProcessorCommand $command) {
            return $command->execute($carryImageStream);
        }, $imageStream);
    }
}
