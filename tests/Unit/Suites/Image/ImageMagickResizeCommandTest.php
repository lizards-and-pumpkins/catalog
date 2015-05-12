<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickResizeCommand
 * @covers \Brera\Image\ResizeCommandTrait
 */
class ImageMagickResizeCommandTest extends AbstractResizeCommandTest
{
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return ImageMagickResizeCommand::class;
    }
}
