<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\Exception\UnableToFindTransformationException;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry
 */
class FacetFieldTransformationRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFieldTransformationRegistry
     */
    private $registry;

    /**
     * @var FacetFieldTransformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldTransformation;

    protected function setUp()
    {
        $this->registry = new FacetFieldTransformationRegistry;
        $this->stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     * @param mixed $invalidCode
     */
    public function testExceptionIsThrownDuringAttemptToRegisterTransformationWithInvalidCode($invalidCode)
    {
        $this->setExpectedException(InvalidTransformationCodeException::class);
        $this->registry->register($invalidCode, $this->stubFacetFieldTransformation);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     * @param mixed $invalidCode
     */
    public function testExceptionIsThrownDuringAttempToRetrieveTransformationByInvalidCode($invalidCode)
    {
        $this->setExpectedException(InvalidTransformationCodeException::class);
        $this->registry->getTransformationByCode($invalidCode);
    }

    /**
     * @return array[]
     */
    public function invalidTransformationCodeDataProvider()
    {
        return [
            [''],
            [' '],
            [null],
            [['foo']]
        ];
    }

    public function testExceptionIsThrownIfNoTransformationWithGivenCodeIsRegistered()
    {
        $this->setExpectedException(UnableToFindTransformationException::class);
        $code = 'foo';
        $this->assertSame($this->stubFacetFieldTransformation, $this->registry->getTransformationByCode($code));
    }

    public function testTransformationCanBeRetrievedByCode()
    {
        $code = 'foo';
        $this->registry->register($code, $this->stubFacetFieldTransformation);
        $this->assertSame($this->stubFacetFieldTransformation, $this->registry->getTransformationByCode($code));
    }
}
