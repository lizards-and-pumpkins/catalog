<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\ContextPartBuilder;

/**
 * @covers \LizardsAndPumpkins\Context\Website\IntegrationTestContextWebsite
 */
class IntegrationTestContextWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestContextWebsite
     */
    private $contextWebsite;

    protected function setUp()
    {
        $this->contextWebsite = new IntegrationTestContextWebsite();
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextWebsite);
    }

    public function testItReturnsTheWebsiteCode()
    {
        $this->assertSame(Website::CONTEXT_CODE, $this->contextWebsite->getCode());
    }

    /**
     * @dataProvider websiteCodeProvider
     */
    public function testItReturnsTheWebsiteIfPresentInTheInput(string $websiteCode)
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

    public function testItReturnsDefaultWebsiteCodeIfNotExplicitlySet()
    {
        $inputDataSet = [];
        $this->assertSame('fr', $this->contextWebsite->getValue($inputDataSet));
    }
}
