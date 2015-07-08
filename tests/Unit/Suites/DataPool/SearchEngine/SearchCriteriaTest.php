<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria
 */
class SearchCriteriaTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, SearchCriteria::createAnd());
    }

    public function testCriteriaAndConditionIsReturned()
    {
        $criteria = SearchCriteria::createAnd();
        $this->assertEquals(SearchCriteria::AND_CONDITION, $criteria->getCondition());
    }

    public function testCriteriaOrConditionIsReturned()
    {
        $criteria = SearchCriteria::createOr();
        $this->assertEquals(SearchCriteria::OR_CONDITION, $criteria->getCondition());
    }

    public function testCriteriaArrayIsReturned()
    {
        $mockCriterion1 = $this->getMock(SearchCriterion::class, [], [], '', false);
        $mockCriterion2 = $this->getMock(SearchCriterion::class, [], [], '', false);

        $criteria = SearchCriteria::createAnd();
        $criteria->add($mockCriterion1);
        $criteria->add($mockCriterion2);

        $result = $criteria->getCriteria();

        $this->assertEquals([$mockCriterion1, $mockCriterion2], $result);
    }

    public function testArrayRepresentationOfCriteriaIsReturned()
    {
        $criteria = SearchCriteria::createAnd();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => SearchCriteria::AND_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }
}
