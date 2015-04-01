<?php

namespace Brera\ImageImport;

use Brera\ImageProcessor\ImageProcessor;

class ImageProcessCommand
{
    /**
     * @var mixed[]
     */
    private $instructions;

    /**
     * @param mixed[] $config
     */
    private function __construct(array $config)
    {
        $this->instructions = $config;
    }

    /**
     * @param mixed[] $config
     * @return ImageProcessCommand
     * @throws InvalidInstructionException
     */
    public static function createByArray($config)
    {
        if (!is_array($config)) {
            throw new InvalidInstructionException('The passed instruction is no array.');
        }
        foreach ($config as $instruction => $parameters) {
            // TODO check the parameters too? The only solution would be Reflection... I don't like.
            $methods = get_class_methods(ImageProcessor::class);
            if (!in_array($instruction, $methods)) {
                throw new InvalidInstructionException(
                    sprintf('"The instruction "%s" doesn\'t exist for image processing.', $instruction)
                );
            }
        }

        return new self($config);
    }

    /**
     * @return mixed[]
     */
    public function getInstructions()
    {
        return $this->instructions;
    }
}
