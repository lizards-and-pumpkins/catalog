<?php

namespace Brera;

/**
 * @covers \Brera\Image
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    public function testImagePathIsPrefixedWithMediaDirectory()
    {
        $image = new Image('foo.png');
        $this->assertEquals(Image::MEDIA_DIR . DIRECTORY_SEPARATOR . 'foo.png', $image->getSrc());
    }

    public function testImageLabelIsReturned()
    {
        $image = new Image('foo.png', 'bar');
        $this->assertEquals('bar', $image->getLabel());
    }
}
