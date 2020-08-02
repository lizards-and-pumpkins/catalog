<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class SearchEngineConfigurationTest extends TestCase
{
    private $testProductsPerPage = 20;

    private $testMaxProductsPerPage = 2000;

    /**
     * @var SortBy|MockObject
     */
    private $dummySortBy;

    /**
     * @var SearchEngineConfiguration
     */
    private $configuration;

    /**
     * @var string[]
     */
    private $testSortableAttributeCodes = ['foo', 'bar'];

    final protected function setUp(): void
    {
        $this->dummySortBy = $this->createMock(SortBy::class);

        $this->configuration = new SearchEngineConfiguration(
            $this->testProductsPerPage,
            $this->testMaxProductsPerPage,
            $this->dummySortBy,
            ...$this->testSortableAttributeCodes
        );
    }

    public function testThrowsAnErrorIfNumberOfProductPerPageTypeIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration('foo', $this->testMaxProductsPerPage, $this->dummySortBy);
    }
    
    public function testReturnsNumberOfProductsPerPage(): void
    {
        $this->assertSame($this->testProductsPerPage, $this->configuration->getProductsPerPage());
    }

    public function testThrowsAnErrorIfMaxProductsPerPageTypeIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, new \stdClass(), $this->dummySortBy);
    }

    public function testReturnsMaxProductsPerPage(): void
    {
        $this->assertSame($this->testMaxProductsPerPage, $this->configuration->getMaxProductsPerPage());
    }
    public function testThrowsAnExceptionIfNonIntegerIsTestedAgainstMaxProductsPerPage(): void
    {
        $this->expectException(\TypeError::class);
        $this->configuration->isExceedingMaxProductsPerPage('1');
    }

    public function testReturnsTrueIfGivenNumberExceedsAllowedNumberOfProductsPerPage(): void
    {
        $this->assertTrue($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage + 1));
    }

    public function testReturnsFalseIfGivenNumberIsLessThenAllowedNumberOfProductsPerPage(): void
    {
        $this->assertFalse($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage - 1));
    }

    public function testReturnsFalseIfGivenNumberEqualsAllowedNumberOfProductsPerPage(): void
    {
        $this->assertFalse($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage));
    }

    public function testThrowsAnErrorIfSortByTypeIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, $this->testMaxProductsPerPage, 'foo');
    }

    public function testReturnsSortBy(): void
    {
        $this->assertSame($this->dummySortBy, $this->configuration->getSortBy());
    }

    public function testThrowsAnErrorIfSortableAttributeCodesTypeIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, $this->testMaxProductsPerPage, $this->dummySortBy, 1);
    }

    public function testThrowsAnErrorIfNonAttributeCodeIsTestedToBeAmongAllowedToBeSortedBy(): void
    {
        $this->expectException(\TypeError::class);
        $this->configuration->isSortingByAttributeAllowed('foo');
    }

    public function testReturnsFalseIfGivenAttributeCodeIsNotAmongSortableAttributes(): void
    {
        $this->assertFalse($this->configuration->isSortingByAttributeAllowed(AttributeCode::fromString('baz')));
    }

    public function testReturnsTrueIfGivenAttributeCodeIsAmongSortableAttributes(): void
    {
        $this->assertTrue($this->configuration->isSortingByAttributeAllowed(AttributeCode::fromString('foo')));
    }
}
