<?php

namespace Brera\Image;

trait ResizeInstructionTrait
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @throws InvalidImageDimensionException
     */
    private function validateImageDimensions()
    {
        if (!is_int($this->width)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image width, got %s.', gettype($this->width))
            );
        }

        if (!is_int($this->height)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image height, got %s.', gettype($this->height))
            );
        }

        if ($this->width <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image width should be greater then zero, got %s.', $this->width)
            );
        }

        if ($this->height <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image height should be greater then zero, got %s.', $this->height)
            );
        }
    }
}
