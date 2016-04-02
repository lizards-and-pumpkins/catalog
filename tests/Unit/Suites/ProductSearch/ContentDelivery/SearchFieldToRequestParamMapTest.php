<?php

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSearchFieldToQueryParameterMapException;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap
 */
class SearchFieldToRequestParamMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchFieldToRequestParamMap
     */
    private $map;

    protected function setUp()
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

    public function testItIsASearchFieldToRequestParamMap()
    {
        $this->assertInstanceOf(SearchFieldToRequestParamMap::class, $this->map);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldMapArrayHasNumericKeys()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            sprintf('The Search Field to Query Parameter Map must have string keys, got "%s"', 0)
        );
        new SearchFieldToRequestParamMap([0 => 'test'], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldMapArrayHasNonStringValues()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            sprintf('The Search Field to Query Parameter Map must have string values, got "integer"')
        );
        new SearchFieldToRequestParamMap(['test' => 0], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldArrayHasAnEmptyStringKey()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Search Field to Query Parameter Map must have not have empty string keys'
        );
        new SearchFieldToRequestParamMap(['' => 'Empty Key'], []);
    }

    public function testItThrowsAnExceptionIfTheSearchFieldArrayHasAnEmptyStringValue()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Search Field to Query Parameter Map must have not have empty string values'
        );
        new SearchFieldToRequestParamMap(['empty_value' => ''], []);
    }
    
    public function testItThrowsAnExceptionIfTheQueryParameterMapArrayHasNumericKeys()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            sprintf('The Query Parameter to Search Field Map must have string keys, got "%s"', 0)
        );
        new SearchFieldToRequestParamMap([], [0 => 'test']);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterMapArrayHasNonStringValues()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            sprintf('The Query Parameter to Search Field Map must have string values, got "integer"')
        );
        new SearchFieldToRequestParamMap([], ['test' => 0]);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterArrayHasAnEmptyStringKey()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Query Parameter to Search Field Map must have not have empty string keys'
        );
        new SearchFieldToRequestParamMap([], ['' => 'Empty Key']);
    }

    public function testItThrowsAnExceptionIfTheQueryParameterHasAnEmptyStringValue()
    {
        $this->expectException(InvalidSearchFieldToQueryParameterMapException::class);
        $this->expectExceptionMessage(
            'The Query Parameter to Search Field Map must have not have empty string values'
        );
        new SearchFieldToRequestParamMap([], ['empty_value' => '']);
    }

    public function testItReturnsTheMatchingQueryParameter()
    {
        $this->assertSame('query_parameter_b', $this->map->getQueryParameterName('search_field_a'));
        $this->assertSame('query_parameter_d', $this->map->getQueryParameterName('search_field_c'));
    }

    public function testItReturnsTheInputValueIfThereIsNoValueInTheMap()
    {
        $this->assertSame('not_defined', $this->map->getQueryParameterName('not_defined'));
        $this->assertSame('not_defined', $this->map->getSearchFieldName('not_defined'));
    }

    public function testItReturnsTheMatchingFacetField()
    {
        $this->assertSame('search_field_b', $this->map->getSearchFieldName('query_parameter_a'));
        $this->assertSame('search_field_d', $this->map->getSearchFieldName('query_parameter_c'));
    }
}
