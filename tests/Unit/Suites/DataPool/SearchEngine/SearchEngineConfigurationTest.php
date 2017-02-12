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
     * @var SortBy|\PHPUnit_Framework_MockObject_MockObject
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

    final protected function setUp()
    {
        $this->dummySortBy = $this->createMock(SortBy::class);

        $this->configuration = new SearchEngineConfiguration(
            $this->testProductsPerPage,
            $this->testMaxProductsPerPage,
            $this->dummySortBy,
            ...$this->testSortableAttributeCodes
        );
    }

    public function testThrowsAnErrorIfNumberOfProductPerPageTypeIsInvalid()
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration('foo', $this->testMaxProductsPerPage, $this->dummySortBy);
    }
    
    public function testReturnsNumberOfProductsPerPage()
    {
        $this->assertSame($this->testProductsPerPage, $this->configuration->getProductsPerPage());
    }

    public function testThrowsAnErrorIfMaxProductsPerPageTypeIsInvalid()
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, new \stdClass(), $this->dummySortBy);
    }

    public function testReturnsMaxProductsPerPage()
    {
        $this->assertSame($this->testMaxProductsPerPage, $this->configuration->getMaxProductsPerPage());
    }
    public function testThrowsAnExceptionIfNonIntegerIsTestedAgainstMaxProductsPerPage()
    {
        $this->expectException(\TypeError::class);
        $this->configuration->isExceedingMaxProductsPerPage('1');
    }

    public function testReturnsTrueIfGivenNumberExceedsAllowedNumberOfProductsPerPage()
    {
        $this->assertTrue($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage + 1));
    }

    public function testReturnsFalseIfGivenNumberIsLessThenAllowedNumberOfProductsPerPage()
    {
        $this->assertFalse($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage - 1));
    }

    public function testReturnsFalseIfGivenNumberEqualsAllowedNumberOfProductsPerPage()
    {
        $this->assertFalse($this->configuration->isExceedingMaxProductsPerPage($this->testMaxProductsPerPage));
    }

    public function testThrowsAnErrorIfSortByTypeIsInvalid()
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, $this->testMaxProductsPerPage, 'foo');
    }

    public function testReturnsSortBy()
    {
        $this->assertSame($this->dummySortBy, $this->configuration->getSortBy());
    }

    public function testThrowsAnErrorIfSortableAttributeCodesTypeIsInvalid()
    {
        $this->expectException(\TypeError::class);
        new SearchEngineConfiguration($this->testProductsPerPage, $this->testMaxProductsPerPage, $this->dummySortBy, 1);
    }

    public function testThrowsAnErrorIfNonAttributeCodeIsTestedToBeAmongAllowedToBeSortedBy()
    {
        $this->expectException(\TypeError::class);
        $this->configuration->isSortingByAttributeAllowed('foo');
    }

    public function testReturnsFalseIfGivenAttributeCodeIsNotAmongSortableAttributes()
    {
        $this->assertFalse($this->configuration->isSortingByAttributeAllowed(AttributeCode::fromString('baz')));
    }

    public function testReturnsTrueIfGivenAttributeCodeIsAmongSortableAttributes()
    {
        $this->assertTrue($this->configuration->isSortingByAttributeAllowed(AttributeCode::fromString('foo')));
    }
}
