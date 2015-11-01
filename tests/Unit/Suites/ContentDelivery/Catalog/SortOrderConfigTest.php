<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSortingDirectionsException;
use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 */
class SortOrderConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SortOrderConfig
     */
    private $sortOrderConfig;

    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode
     */
    private $stubAttributeCode;

    /**
     * @var string[]
     */
    private $testDirections = ['asc', 'desc'];

    /**
     * @var string
     */
    private $testSelectedDirection = 'asc';

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->sortOrderConfig = SortOrderConfig::create(
            $this->stubAttributeCode,
            $this->testDirections,
            $this->testSelectedDirection
        );
    }

    /**
     * @dataProvider invalidDirectionsProvider
     * @param mixed[] $directions
     */
    public function testExceptionIsThrownDuringAttemptToCreateSortOrderConfigWithInvalidDirections(array $directions)
    {
        $this->setExpectedException(InvalidSortingDirectionsException::class);
        SortOrderConfig::create($this->stubAttributeCode, $directions, $this->testSelectedDirection);
    }

    /**
     * @return array[]
     */
    public function invalidDirectionsProvider()
    {
        return [
            [[]],
            [['foo']],
            [['asc', 'foo']],
        ];
    }

    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $directions = ['asc'];
        $selectedDirection = 'desc';
        $this->setExpectedException(InvalidSortingDirectionsException::class);
        SortOrderConfig::create($this->stubAttributeCode, $directions, $selectedDirection);
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->sortOrderConfig->getAttributeCode());
    }

    public function testSortingDirectionsAreReturned()
    {
        $this->assertSame($this->testDirections, $this->sortOrderConfig->getDirections());
    }

    public function testSelectedDirectionIsReturned()
    {
        $this->assertSame($this->testSelectedDirection, $this->sortOrderConfig->getSelectedDirection());
    }
}
