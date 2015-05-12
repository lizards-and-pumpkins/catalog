<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickResizeCommand
 * @uses \Brera\Image\ResizeCommandTrait
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
