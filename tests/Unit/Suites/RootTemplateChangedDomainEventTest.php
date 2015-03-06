<?php

namespace Brera;

/**
 * @covers \Brera\RootTemplateChangedDomainEvent
 */
class RootTemplateChangedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnImportXml()
    {
        $xml = '<root></root>';

        $event = new RootTemplateChangedDomainEvent($xml);
        $result = $event->getXml();

        $this->assertEquals($xml, $result);
    }
}
