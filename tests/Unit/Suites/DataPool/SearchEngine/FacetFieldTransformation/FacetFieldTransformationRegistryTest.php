<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\UnableToFindTransformationException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry
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
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->register($invalidCode, $this->stubFacetFieldTransformation);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     * @param mixed $invalidCode
     */
    public function testExceptionIsThrownDuringAttemptToRetrieveTransformationByInvalidCode($invalidCode)
    {
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->getTransformationByCode($invalidCode);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     * @param mixed $invalidCode
     */
    public function testExceptionIsThrownDuringAttemptToCheckIfTransformationForInvalidCodeIsRegistered($invalidCode)
    {
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->hasTransformationForCode($invalidCode);
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

    public function testFalseIsReturnedIfNoTransformationWithGivenCodeIsRegistered()
    {
        $code = 'foo';
        $this->assertFalse($this->registry->hasTransformationForCode($code));
    }

    public function testTrueIsReturnedIfTransformationWithGivenCodeIsRegistered()
    {
        $code = 'foo';
        $this->registry->register($code, $this->stubFacetFieldTransformation);
        $this->assertTrue($this->registry->hasTransformationForCode($code));
    }

    public function testExceptionIsThrownIfNoTransformationWithGivenCodeIsRegistered()
    {
        $this->expectException(UnableToFindTransformationException::class);
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
