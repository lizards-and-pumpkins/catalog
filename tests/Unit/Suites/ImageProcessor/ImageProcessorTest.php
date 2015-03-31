<?php

namespace Brera\ImageProcessor;

abstract class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessor
     */
    protected $processor;

    const IMAGE = __DIR__ . '/../../../shared-fixture/flower.jpg';

    abstract function getClassName();

    protected function setUp()
    {
        $classname = $this->getClassName();
        $this->processor = new $classname(self::IMAGE);
    }

    /**
     * @test
     */
    public function itShouldImplementImageProcessorInterface()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->processor);
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageException
     */
    public function itShouldThrowAnExceptionWhenImagePathIsInvalid()
    {
        $classname = $this->getClassName();
        $this->processor = new $classname('some/path/image.jpg');
    }

    /**
     * @test
     */
    public function itShouldSaveAProcessedImage()
    {
        $filename = tempnam(sys_get_temp_dir(), 'image_processor_test_');
        $this->processor->saveAsFile($filename);
        $this->assertTrue(is_file($filename));
        $this->assertEquals(getimagesize($filename), getimagesize(self::IMAGE));
    }

    protected function tearDown()
    {
        $files = glob(sys_get_temp_dir() . '/image_processor_test_*');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
