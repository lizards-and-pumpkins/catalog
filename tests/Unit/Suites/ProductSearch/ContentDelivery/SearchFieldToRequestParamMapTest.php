<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSearchFieldToQueryParameterMapException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap
 */
class SearchFieldToRequestParamMapTest extends TestCase
{
    /**
     * @var SearchFieldToRequestParamMap
     */
    private $map;

    final protected function setUp(): void
    {
        $searchFieldToQueryParameterMap = [
            'search_field_a' => 'query_parameter_b',
            'search_field_c' => 'query_parameter_d',
        ];
        $queryParameterToFacetFieldMap = [
            'query_parameter_a' => 'search_field_b',
            'query_parameter_c' => 'search_field_d',
        ];
        $this->map = new SearchFieldToRequestParamMap(
            $searchFieldToQueryParameterMap,
            $queryParameterToFacetFieldMap
        );
    }

    public function testItIsASearchFieldToRequestParamMap(): void
    {
        $this->assertInstanceOf(SearchFieldToRequestParamMap::class, $this->map);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldMapArrayHasNumericKeys(): void
    {
        $this->expectException(\TypeError::class);
        new SearchFieldToRequestParamMap([0 => 'test'], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldMapArrayHasNonStringValues(): void
    {
        $this->expectException(\TypeError::class);
        new SearchFieldToRequestParamMap(['test' => 0], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldArrayHasAnEmptyStringKey(): void
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Search Field to Query Parameter Map must have not have empty string keys'
        );
        new SearchFieldToRequestParamMap(['' => 'Empty Key'], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldArrayHasAnEmptyStringValue(): void
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Search Field to Query Parameter Map must have not have empty string values'
        );
        new SearchFieldToRequestParamMap(['empty_value' => ''], []);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterMapArrayHasNumericKeys(): void
    {
        $this->expectException(\TypeError::class);
        new SearchFieldToRequestParamMap([], [0 => 'test']);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterMapArrayHasNonStringValues(): void
    {
        $this->expectException(\TypeError::class);
        new SearchFieldToRequestParamMap([], ['test' => 0]);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterArrayHasAnEmptyStringKey(): void
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Query Parameter to Search Field Map must have not have empty string keys'
        );
        new SearchFieldToRequestParamMap([], ['' => 'Empty Key']);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterHasAnEmptyStringValue(): void
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Query Parameter to Search Field Map must have not have empty string values'
        );
        new SearchFieldToRequestParamMap([], ['empty_value' => '']);
    }

    public function testItReturnsTheMatchingQueryParameter(): void
    {
        $this->assertSame('query_parameter_b', $this->map->getQueryParameterName('search_field_a'));
        $this->assertSame('query_parameter_d', $this->map->getQueryParameterName('search_field_c'));
    }

    public function testItReturnsTheInputValueIfThereIsNoValueInTheMap(): void
    {
        $this->assertSame('not_defined', $this->map->getQueryParameterName('not_defined'));
        $this->assertSame('not_defined', $this->map->getSearchFieldName('not_defined'));
    }

    public function testItReturnsTheMatchingFacetField(): void
    {
        $this->assertSame('search_field_b', $this->map->getSearchFieldName('query_parameter_a'));
        $this->assertSame('search_field_d', $this->map->getSearchFieldName('query_parameter_c'));
    }
}
