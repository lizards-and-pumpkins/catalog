<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionCountException;
use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionValueException;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilterOption
 */
class FilterNavigationFilterOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerializableInterfaceIsImplemented()
    {
        $optionValue = 1;
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::create($optionValue, $optionCount);

        $this->assertInstanceOf(\JsonSerializable::class, $filterOption);
    }

    public function testExceptionIsThrownIfOptionValueIsNeitherStringNorInteger()
    {
        $invalidOptionValue = 1.5;
        $optionCount = 1;

        $this->setExpectedException(InvalidFilterNavigationFilterOptionValueException::class);
        FilterNavigationFilterOption::create($invalidOptionValue, $optionCount);
    }

    public function testExceptionIsThrownIfOptionCountIsNotInteger()
    {
        $optionValue = 'bar';
        $invalidOptionCount = '1';

        $this->setExpectedException(InvalidFilterNavigationFilterOptionCountException::class);
        FilterNavigationFilterOption::create($optionValue, $invalidOptionCount);
    }

    public function testOptionIsCreated()
    {
        $optionValue = 1;
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::create($optionValue, $optionCount);

        $this->assertSame($optionValue, $filterOption->getValue());
        $this->assertSame($optionCount, $filterOption->getCount());
    }

    public function testArrayRepresentationOfFilterOptionIsReturned()
    {
        $optionValue = 1;
        $optionCount = 1;
        $filterOption = FilterNavigationFilterOption::create($optionValue, $optionCount);

        $expectedArray = [
            'value' => $optionValue,
            'count' => $optionCount,
        ];

        $this->assertSame($expectedArray, $filterOption->jsonSerialize());
    }
}
