<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;

/**
 * @covers \LizardsAndPumpkins\SampleContextSource
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 */
class SampleContextSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectedContextMatrixIsReturned()
    {
        $expectedContextMatrix = [
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'en_US'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'en_US'],
        ];

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class);
        $stubContextBuilder->expects($this->once())
            ->method('createContextsFromDataSets')
            ->with($expectedContextMatrix);

        (new SampleContextSource($stubContextBuilder))->getAllAvailableContexts();
    }
}
