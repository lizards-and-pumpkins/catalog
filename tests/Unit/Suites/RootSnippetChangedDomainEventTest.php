<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetChangedDomainEvent
 */
class RootSnippetChangedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnImportXml()
    {
        $xml = '<root></root>';

        $event = new RootSnippetChangedDomainEvent($xml);
        $result = $event->getXml();

        $this->assertEquals($xml, $result);
    }
}
