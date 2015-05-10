<?php

namespace Brera\ImageProcessor;

/**
 * @covers \Brera\ImageProcessor\GdResizeCommand
 */
class GdResizeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementImageProcessorCommandInterface()
    {
        $command = new GdResizeCommand(1, 1);
        $this->assertInstanceOf(ImageProcessorCommand::class, $command);
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image width, got string.
     */
    public function itShouldFailIfWidthIsNotAnInteger()
    {
        (new GdResizeCommand('foo', 1))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageDimensionException
     * @expectedExceptionMessage Image width should be greater then zero, got 0.
     */
    public function itShouldFailIfWidthIsNotPositive()
    {
        (new GdResizeCommand(0, 1))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image height, got string.
     */
    public function itShouldFailIfHeightIsNotAnInteger()
    {
        (new GdResizeCommand(1, 'foo'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageDimensionException
     * @expectedExceptionMessage Image height should be greater then zero, got -1.
     */
    public function itShouldFailIfHeightIsNotPositive()
    {
        (new GdResizeCommand(1, -1))->execute('');
    }
    
    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageStreamException
     */
    public function itShouldFailIfImageStreamIsNotValid()
    {
        (new GdResizeCommand(1, 1))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageStreamException
     */
    public function itShouldFailIfImageFormatIsNotSupported()
    {
        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/blank.ico');
        $base64EncodedImageStream = base64_encode($imageStream);

        (new GdResizeCommand(1, 1))->execute($base64EncodedImageStream);
    }

    /**
     * @test
     */
    public function itShouldResizeImageToGivenDimensions()
    {
        $requiredImageWidth = 15;
        $requiredImageHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/test_image2.jpg');
        $base64EncodedImageStream = base64_encode($imageStream);

        $result = (new GdResizeCommand($requiredImageWidth, $requiredImageHeight))->execute($base64EncodedImageStream);
        $decodedResult = base64_decode($result);
        $resultImageInfo = getimagesizefromstring($decodedResult);

        $this->assertEquals($requiredImageWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredImageHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
