<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;


abstract class AbstractResizeStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testImageProcessorStrategyInterfaceIsImplemented()
    {
        $class = $this->getResizeClassName();
        $strategy = new $class(1, 1);
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    public function testExceptionIsThrownIfWidthIsNotAnInteger()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Expected integer as image width, got string.');
        $class = $this->getResizeClassName();
        (new $class('foo', 1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfWidthIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image width should be greater then zero, got 0.');
        $class = $this->getResizeClassName();
        (new $class(0, 1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Expected integer as image height, got string.');
        $class = $this->getResizeClassName();
        (new $class(1, 'foo'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image height should be greater then zero, got -1.');
        $class = $this->getResizeClassName();
        (new $class(1, -1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid()
    {
        $this->expectException(InvalidBinaryImageDataException::class);
        $class = $this->getResizeClassName();
        (new $class(1, 1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported()
    {
        $this->expectException(InvalidBinaryImageDataException::class);
        
        $imageStream = file_get_contents(__DIR__ . '/../../../../../shared-fixture/blank.ico');

        $class = $this->getResizeClassName();
        (new $class(1, 1))->processBinaryImageData($imageStream);
    }

    public function testImageIsResizedToGivenDimensions()
    {
        $requiredImageWidth = 15;
        $requiredImageHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../../../shared-fixture/test_image2.jpg');

        $class = $this->getResizeClassName();
        $result = (new $class($requiredImageWidth, $requiredImageHeight))->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredImageWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredImageHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }

    /**
     * @return string
     */
    abstract protected function getResizeClassName();
}
