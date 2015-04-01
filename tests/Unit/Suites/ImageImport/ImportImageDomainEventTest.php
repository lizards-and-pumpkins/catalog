<?php

namespace Brera\ImageImport;

/**
 * @covers Brera\Product\ImportImageEventTest
 */
class ImportImageDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider invalidImagesDataProvider
     * @expectedException \Brera\ImageProcessor\InvalidImageException
     * @param mixed[] $invalidParameter
     */
    public function itShouldThrowAnExceptionIfImageIsNotReadable($invalidParameter)
    {
        ImportImageDomainEvent::fromImages($invalidParameter);
    }

    /**
     * @return mixed[]
     */
    public function invalidImagesDataProvider()
    {
        return [
            array([0.00]),
            array([0]),
            array([new \stdClass()]),
            array([tmpfile()]),
            array(['non-existing-images.jpg']),
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnPassedImages()
    {
        $images = array(__DIR__ . '/../../../shared-fixture/test_image.jpg');
        $event = ImportImageDomainEvent::fromImages($images);
        $this->assertInstanceOf(ImportImageDomainEvent::class, $event);
        $this->assertEquals($images, $event->getImages());
    }
}
