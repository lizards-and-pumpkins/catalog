<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Locale\IntegrationTestContextLocale
 */
class IntegrationTestContextLocaleTest extends TestCase
{
    /**
     * @var IntegrationTestContextLocale
     */
    private $contextLocale;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->contextLocale = new IntegrationTestContextLocale();
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextLocale);
    }

    public function testItReturnsTheCode()
    {
        $this->assertSame(Locale::CONTEXT_CODE, $this->contextLocale->getCode());
    }

    public function testItReturnsTheDefaultLocaleIfItCanNotBeDeterminedFromTheInputDataSets()
    {
        $inputDataSet = [];
        $this->assertSame('fr_FR', $this->contextLocale->getValue($inputDataSet));
    }

    public function testItReturnsTheLocaleFromTheInputArrayIfItIsPresent()
    {
        $inputDataSet = [Locale::CONTEXT_CODE => 'xx_XX'];
        $this->assertSame('xx_XX', $this->contextLocale->getValue($inputDataSet));
    }
}
