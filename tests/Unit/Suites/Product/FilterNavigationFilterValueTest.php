<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\FilterNavigationFilterValue
 */
class FilterNavigationFilterValueTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfValueIsNotString()
    {
        $invalidValue = 1;
        $count = 1;

        $this->setExpectedException(InvalidFilterNavigationFilterValueValueException::class);
        FilterNavigationFilterValue::create($invalidValue, $count);
    }

    public function testExceptionIsThrownIfCountIsNotInteger()
    {
        $value = 'foo';
        $invalidCount = '1';

        $this->setExpectedException(InvalidFilterNavigationFilterValueCountException::class);
        FilterNavigationFilterValue::create($value, $invalidCount);
    }

    public function testValueIsCreated()
    {
        $value = 'foo';
        $count = 1;
        $filterValue = FilterNavigationFilterValue::create($value, $count);

        $this->assertSame($value, $filterValue->getValue());
        $this->assertSame($count, $filterValue->getCount());
        $this->assertFalse($filterValue->isSelected());
    }

    public function testSelectedValueIsCreated()
    {
        $value = 'foo';
        $count = 1;
        $filterValue = FilterNavigationFilterValue::createSelected($value, $count);

        $this->assertSame($value, $filterValue->getValue());
        $this->assertSame($count, $filterValue->getCount());
        $this->assertTrue($filterValue->isSelected());
    }
}
