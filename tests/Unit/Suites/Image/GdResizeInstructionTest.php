<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\GdResizeInstruction
 * @covers \Brera\Image\ResizeInstructionTrait
 */
class GdResizeInstructionTest extends AbstractResizeInstructionTest
{
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return GdResizeInstruction::class;
    }
}
