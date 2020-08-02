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
     * @var HttpRequest|MockObject
     */
    private $stubRequest;

    final protected function setUp(): void
    {
        $this->contextLocale = new IntegrationTestContextLocale();
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testItIsAContextPartBuilder(): void
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextLocale);
    }

    public function testItReturnsTheCode(): void
    {
        $this->assertSame(Locale::CONTEXT_CODE, $this->contextLocale->getCode());
    }

    public function testItReturnsTheDefaultLocaleIfItCanNotBeDeterminedFromTheInputDataSets(): void
    {
        $inputDataSet = [];
        $this->assertSame('fr_FR', $this->contextLocale->getValue($inputDataSet));
    }

    public function testItReturnsTheLocaleFromTheInputArrayIfItIsPresent(): void
    {
        $inputDataSet = [Locale::CONTEXT_CODE => 'xx_XX'];
        $this->assertSame('xx_XX', $this->contextLocale->getValue($inputDataSet));
    }
}
