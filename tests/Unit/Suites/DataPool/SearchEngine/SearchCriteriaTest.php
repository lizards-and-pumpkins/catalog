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

    public function testCriteriaWithAndConditionIsCreated()
    {
        $criteria = SearchCriteria::createAnd();

        $this->assertTrue($criteria->hasAndCondition());
        $this->assertFalse($criteria->hasOrCondition());
    }

    public function testCriteriaWithOrConditionIsCreate()
    {
        $criteria = SearchCriteria::createOr();

        $this->assertTrue($criteria->hasOrCondition());
        $this->assertFalse($criteria->hasAndCondition());
    }

    public function testCriteriaArrayIsReturned()
    {
        $mockCriterion1 = $this->getMock(SearchCriterion::class, [], [], '', false);
        $mockCriterion2 = $this->getMock(SearchCriterion::class, [], [], '', false);

        $criteria = SearchCriteria::createAnd();
        $criteria->addCriterion($mockCriterion1);
        $criteria->addCriterion($mockCriterion2);

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
