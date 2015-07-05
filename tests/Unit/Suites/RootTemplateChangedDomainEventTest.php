<?php

namespace Brera;

/**
 * @covers \Brera\RootTemplateChangedDomainEvent
 */
class RootTemplateChangedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testPassedInXmlIsReturned()
    {
        $xml = 'foo';

        $event = new RootTemplateChangedDomainEvent($xml);
        $result = $event->getXml();

        $this->assertEquals($xml, $result);
    }
}
