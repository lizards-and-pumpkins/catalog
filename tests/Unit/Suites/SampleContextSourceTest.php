<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\LocaleContextDecorator;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;

/**
 * @covers \LizardsAndPumpkins\SampleContextSource
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 */
class SampleContextSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectedContextMatrixIsReturned()
    {
        $expectedContextMatrix = [
            [WebsiteContextDecorator::CODE => 'ru', LocaleContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'ru', LocaleContextDecorator::CODE => 'en_US'],
            [WebsiteContextDecorator::CODE => 'cy', LocaleContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'cy', LocaleContextDecorator::CODE => 'en_US'],
        ];

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $stubContextBuilder->expects($this->once())
            ->method('createContextsFromDataSets')
            ->with($expectedContextMatrix);

        (new SampleContextSource($stubContextBuilder))->getAllAvailableContexts();
    }
}
