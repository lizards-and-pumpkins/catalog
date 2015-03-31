<?php

namespace Brera\ImageProcessor;

abstract class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessor
     */
    private $processor;

    const IMAGE_UNDER_TEST = __DIR__ . '/../../../shared-fixture/test_image.jpg';

    /**
     * @return ImageProcessor
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param ImageProcessor $processor
     */
    protected function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    abstract protected function getImageProcessorClassName();

    protected function setUp()
    {
        $imageProcessorClassName = $this->getImageProcessorClassName();
        $this->setProcessor(new $imageProcessorClassName(self::IMAGE_UNDER_TEST));
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
    public function itShouldThrowAnExceptionIfImageIsNotReadable()
    {
        $imageProcessorClassName = $this->getImageProcessorClassName();
        $imageProcessorClassName::fromFile('some/path/non-existing-image.jpg');
    }

    /**
     * @test
     */
    public function itShouldSaveAProcessedImage()
    {
        $filename = tempnam(sys_get_temp_dir(), 'image_processor_test_');
        $this->getProcessor()->saveAsFile($filename);
        $this->assertTrue(is_file($filename));
        $this->assertEquals(getimagesize($filename), getimagesize(self::IMAGE_UNDER_TEST));
    }

    protected function tearDown()
    {
        $files = glob(sys_get_temp_dir() . '/image_processor_test_*');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
