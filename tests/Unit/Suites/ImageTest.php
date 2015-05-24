<?php

namespace Brera;

/**
 * @covers \Brera\Image
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldPrependImagePathWithMediaDirectory()
    {
        $image = new Image('foo.png');

        $this->assertEquals(Image::MEDIA_DIR . '/bar/foo.png', $image->getPath('bar'));
    }

    /**
     * @test
     */
    public function itShouldReturnImageLabel()
    {
        $image = new Image('foo.png', 'bar');

        $this->assertEquals('bar', $image->getLabel());
    }
}
