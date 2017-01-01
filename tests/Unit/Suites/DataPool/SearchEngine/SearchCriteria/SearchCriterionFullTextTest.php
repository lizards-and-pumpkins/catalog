<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText
 */
class SearchCriterionFullTextTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, new SearchCriterionFullText());
    }

}
