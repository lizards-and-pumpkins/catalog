<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\GdResizeCommand
 */
class GdResizeCommandTest extends AbstractResizeCommandTest
{
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return GdResizeCommand::class;
    }
}
