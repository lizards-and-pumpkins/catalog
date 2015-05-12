<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickResizeCommand
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
