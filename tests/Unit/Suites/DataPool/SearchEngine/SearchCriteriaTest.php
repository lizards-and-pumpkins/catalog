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
        /** @var SearchCriterion|\PHPUnit_Framework_MockObject_MockObject $mockCriterion1 */
        $mockCriterion1 = $this->getMock(SearchCriterion::class, [], [], '', false);
        /** @var SearchCriterion|\PHPUnit_Framework_MockObject_MockObject $mockCriterion2 */
        $mockCriterion2 = $this->getMock(SearchCriterion::class, [], [], '', false);
        /** @var SearchCriterion|\PHPUnit_Framework_MockObject_MockObject $mockCriterion3 */
        $mockCriterion3 = $this->getMock(SearchCriterion::class, [], [], '', false);

        $nestedCriteria = SearchCriteria::createOr();
        $nestedCriteria->addCriterion($mockCriterion1);
        $nestedCriteria->addCriterion($mockCriterion2);

        $criteria = SearchCriteria::createAnd();
        $criteria->addCriterion($mockCriterion3);
        $criteria->addCriteria($nestedCriteria);

        $result = $criteria->getCriteria();

        $this->assertEquals([$mockCriterion3, $nestedCriteria], $result);
    }

    public function testArrayRepresentationOfCriteriaIsReturned()
    {
        $criteria = SearchCriteria::createAnd();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => SearchCriteria::AND_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }
}
