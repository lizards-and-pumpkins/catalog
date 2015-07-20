<?php

namespace Brera;

/**
 * @covers \Brera\PageTemplateWasUpdatedDomainEvent
 */
class PageTemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testPassedInXmlIsReturned()
    {
        $xml = 'foo';

        $event = new PageTemplateWasUpdatedDomainEvent($xml);
        $result = $event->getXml();

        $this->assertEquals($xml, $result);
    }
}
