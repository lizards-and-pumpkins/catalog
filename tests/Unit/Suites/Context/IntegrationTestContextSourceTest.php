<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\Website\Website;

/**
 * @covers \LizardsAndPumpkins\Context\IntegrationTestContextSource
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 */
class IntegrationTestContextSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectedContextMatrixIsReturned()
    {
        $expectedContextMatrix = [
            [Website::CONTEXT_CODE => 'ru', ContextLocale::CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'ru', ContextLocale::CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'cy', ContextLocale::CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'cy', ContextLocale::CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'fr', ContextLocale::CODE => 'fr_FR'],
        ];

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class);
        $stubContextBuilder->expects($this->once())
            ->method('createContextsFromDataSets')
            ->with($expectedContextMatrix);

        (new IntegrationTestContextSource($stubContextBuilder))->getAllAvailableContexts();
    }
}
