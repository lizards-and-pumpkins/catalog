<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search;

class SearchFieldToRequestParamMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchFieldToRequestParamMap
     */
    private $map;

    protected function setUp()
    {
        $facetFieldToQueryParameterMap = [
            'facet_field_a' => 'query_parameter_b',
            'facet_field_c' => 'query_parameter_d'
        ];
        $queryParameterToFacetFieldMap = [
            'query_parameter_a' => 'facet_field_b',
            'query_parameter_c' => 'facet_field_d',
        ];
        $this->map = new SearchFieldToRequestParamMap(
            $facetFieldToQueryParameterMap,
            $queryParameterToFacetFieldMap
        );
    }
    
    public function testItIsASearchFieldToRequestParamMap()
    {
        $this->assertInstanceOf(SearchFieldToRequestParamMap::class, $this->map);
    }

    public function testItReturnsTheMatchingQueryParameter()
    {
        $this->assertSame('query_parameter_b', $this->map->getQueryParameterName('facet_field_a'));
        $this->assertSame('query_parameter_d', $this->map->getQueryParameterName('facet_field_c'));
    }

    public function testItReturnsTheInputValueIfThereIsNoValueInTheMap()
    {
        $this->assertSame('not_defined', $this->map->getQueryParameterName('not_defined'));
        $this->assertSame('not_defined', $this->map->getFacetFieldName('not_defined'));
    }

    public function testItReturnsTheMatchingFacetField()
    {
        $this->assertSame('facet_field_b', $this->map->getFacetFieldName('query_parameter_a'));
        $this->assertSame('facet_field_d', $this->map->getFacetFieldName('query_parameter_c'));
    }
}
