<?php

namespace Brera\Product;

use Brera\Context\ContextSource;

/**
 * @covers \Brera\Product\ProductWasUpdatedDomainEventHandler
 */
class ProductWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectionIsTriggered()
    {
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);

        $stubDomainEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $stubDomainEvent->expects($this->once())
            ->method('getXml');

        $stubProductBuilder = $this->getMock(ProductSourceBuilder::class);
        $stubProductBuilder->expects($this->once())
            ->method('createProductSourceFromXml')
            ->willReturn($stubProductSource);

        $stubContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextMatrix'])
            ->getMock();

        $stubProjector = $this->getMock(ProductProjector::class, [], [], '', false);
        $stubProjector->expects($this->once())
            ->method('project')
            ->with($stubProductSource, $stubContextSource);

        (new ProductWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $stubProductBuilder,
            $stubContextSource,
            $stubProjector
        )
        )->process();
    }
}
