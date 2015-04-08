<?php

namespace Brera\ImageImport;

/**
 * @covers \Brera\ImageImport\ImportImageDomainEvent
 */
class ImportImageDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnPassedImages()
    {
        $images = array(__DIR__ . '/../../../shared-fixture/test_image.jpg');
        $event = ImportImageDomainEvent::fromArray($images);
        $this->assertInstanceOf(ImportImageDomainEvent::class, $event);
        $this->assertEquals($images, $event->getImages());
    }
}
