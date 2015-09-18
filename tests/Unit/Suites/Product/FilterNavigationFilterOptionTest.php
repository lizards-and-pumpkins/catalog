<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionCountException;
use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionValueException;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilterOption
 */
class FilterNavigationFilterOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfOptionValueIsNeitherStringNorInteger()
    {
        $optionCode = 'foo';
        $invalidOptionValue = 1.5;
        $optionCount = 1;

        $this->setExpectedException(InvalidFilterNavigationFilterOptionValueException::class);
        FilterNavigationFilterOption::create($optionCode, $invalidOptionValue, $optionCount);
    }

    public function testExceptionIsThrownIfOptionCountIsNotInteger()
    {
        $optionCode = 'foo';
        $optionValue = 'bar';
        $invalidOptionCount = '1';

        $this->setExpectedException(InvalidFilterNavigationFilterOptionCountException::class);
        FilterNavigationFilterOption::create($optionCode, $optionValue, $invalidOptionCount);
    }

    public function testOptionIsCreated()
    {
        $optionCode = 'foo';
        $optionValue = 1;
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::create($optionCode, $optionValue, $optionCount);

        $this->assertSame($optionCode, $filterOption->getCode());
        $this->assertSame($optionValue, $filterOption->getValue());
        $this->assertSame($optionCount, $filterOption->getCount());
        $this->assertFalse($filterOption->isSelected());
    }

    public function testSelectedOptionIsCreated()
    {
        $optionCode = 'foo';
        $optionValue = 'bar';
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::createSelected($optionCode, $optionValue, $optionCount);

        $this->assertSame($optionCode, $filterOption->getCode());
        $this->assertSame($optionValue, $filterOption->getValue());
        $this->assertSame($optionCount, $filterOption->getCount());
        $this->assertTrue($filterOption->isSelected());
    }
}
