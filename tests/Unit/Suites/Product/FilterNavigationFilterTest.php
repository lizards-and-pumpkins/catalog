<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterCodeException;
use LizardsAndPumpkins\Renderer\Translation\Translator;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilter
 */
class FilterNavigationFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testFilterNavigationCode = 'bar';

    /**
     * @var FilterNavigationFilter
     */
    private $filter;

    /**
     * @var FilterNavigationFilterOptionCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFilterValueCollection;

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject $stubTranslator
     */
    private $stubTranslator;

    protected function setUp()
    {
        $this->stubFilterValueCollection = $this->getMock(
            FilterNavigationFilterOptionCollection::class,
            [],
            [],
            '',
            false
        );
        $this->stubFilterValueCollection->method('jsonSerialize')->willReturn([]);

        $this->stubTranslator = $this->getMock(Translator::class);

        $this->filter = FilterNavigationFilter::create(
            $this->testFilterNavigationCode,
            $this->stubFilterValueCollection,
            $this->stubTranslator
        );
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->filter);
    }

    public function testExceptionIsThrownDuringAttemptToCreateFilterWithNonStringAttributeCode()
    {
        $this->setExpectedException(InvalidFilterNavigationFilterCodeException::class);
        $invalidFilterNavigationCode = 1;
        FilterNavigationFilter::create(
            $invalidFilterNavigationCode,
            $this->stubFilterValueCollection,
            $this->stubTranslator
        );
    }

    public function testFilterNavigationFilterIsReturned()
    {
        $this->assertSame($this->testFilterNavigationCode, $this->filter->getCode());
        $this->assertSame($this->stubFilterValueCollection, $this->filter->getOptionCollection());
    }

    public function testFilterArrayRepresentationIsReturned()
    {
        $translatedCode = 'bär';
        $this->stubTranslator->method('translate')->with($this->testFilterNavigationCode)->willReturn($translatedCode);

        $expectedArray = [
            'code' => $this->testFilterNavigationCode,
            'label' => $translatedCode,
            'options' => []
        ];

        $this->assertSame($expectedArray, $this->filter->jsonSerialize());
    }
}
