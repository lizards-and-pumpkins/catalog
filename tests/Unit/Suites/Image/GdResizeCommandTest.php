<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\GdResizeCommand
 * @uses \Brera\Image\ResizeCommandTrait
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
