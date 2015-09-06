<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\FilterNavigationFilterOption
 */
class FilterNavigationFilterOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfOptionValueIsNotString()
    {
        $invalidOptionValue = 1;
        $optionCount = 1;

        $this->setExpectedException(InvalidFilterNavigationFilterOptionValueException::class);
        FilterNavigationFilterOption::create($invalidOptionValue, $optionCount);
    }

    public function testExceptionIsThrownIfOptionCountIsNotInteger()
    {
        $optionValue = 'foo';
        $invalidOptionCount = '1';

        $this->setExpectedException(InvalidFilterNavigationFilterOptionCountException::class);
        FilterNavigationFilterOption::create($optionValue, $invalidOptionCount);
    }

    public function testOptionIsCreated()
    {
        $optionValue = 'foo';
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::create($optionValue, $optionCount);

        $this->assertSame($optionValue, $filterOption->getValue());
        $this->assertSame($optionCount, $filterOption->getCount());
        $this->assertFalse($filterOption->isSelected());
    }

    public function testSelectedOptionIsCreated()
    {
        $optionValue = 'foo';
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::createSelected($optionValue, $optionCount);

        $this->assertSame($optionValue, $filterOption->getValue());
        $this->assertSame($optionCount, $filterOption->getCount());
        $this->assertTrue($filterOption->isSelected());
    }
}
