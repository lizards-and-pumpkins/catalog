<?php

namespace Brera\Image;

class ImageProcessorInstructionSequence implements ImageProcessorInstruction
{
    /**
     * @var ImageProcessorInstruction[]
     */
    private $instructions = [];

    public function addInstruction(ImageProcessorInstruction $instruction)
    {
        $this->instructions[] = $instruction;
    }

    /**
     * @param string $imageBinaryData
     * @return string
     */
    public function execute($imageBinaryData)
    {
        return array_reduce($this->instructions, function (
            $carryImageBinaryData, ImageProcessorInstruction $instruction
        ) {
            return $instruction->execute($carryImageBinaryData);
        }, $imageBinaryData);
    }
}
