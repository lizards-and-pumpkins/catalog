<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\ContextPartBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Website\IntegrationTestContextWebsite
 */
class IntegrationTestContextWebsiteTest extends TestCase
{
    /**
     * @var IntegrationTestContextWebsite
     */
    private $contextWebsite;

    final protected function setUp(): void
    {
        $this->contextWebsite = new IntegrationTestContextWebsite();
    }

    public function testItIsAContextPartBuilder(): void
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextWebsite);
    }

    public function testItReturnsTheWebsiteCode(): void
    {
        $this->assertSame(Website::CONTEXT_CODE, $this->contextWebsite->getCode());
    }

    /**
     * @dataProvider websiteCodeProvider
     */
    public function testItReturnsTheWebsiteIfPresentInTheInput(string $websiteCode): void
    {
        $inputDataSet = [Website::CONTEXT_CODE => $websiteCode];
        $this->assertSame($websiteCode, $this->contextWebsite->getValue($inputDataSet));
    }

    /**
     * @return array[]
     */
    public function websiteCodeProvider() : array
    {
        return [['foo'], ['bar']];
    }

    public function testItReturnsDefaultWebsiteCodeIfNotExplicitlySet(): void
    {
        $inputDataSet = [];
        $this->assertSame('fr', $this->contextWebsite->getValue($inputDataSet));
    }
}
