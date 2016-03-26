<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\TwentyOneRunContextSource;
use LizardsAndPumpkins\Context\Website\ContextWebsite;

/**
 * @covers \LizardsAndPumpkins\Context\TwentyOneRunContextSource
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 */
class TwentyOneRunContextSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectedContextMatrixIsReturned()
    {
        $expectedContextMatrix = [
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'en_US'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'en_US'],
            [ContextWebsite::CODE => 'fr', ContextLocale::CODE => 'fr_FR'],
        ];

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class);
        $stubContextBuilder->expects($this->once())
            ->method('createContextsFromDataSets')
            ->with($expectedContextMatrix);

        (new TwentyOneRunContextSource($stubContextBuilder))->getAllAvailableContexts();
    }
}
