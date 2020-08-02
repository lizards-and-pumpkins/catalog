<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\UnableToFindTransformationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry
 */
class FacetFieldTransformationRegistryTest extends TestCase
{
    /**
     * @var FacetFieldTransformationRegistry
     */
    private $registry;

    /**
     * @var FacetFieldTransformation|MockObject
     */
    private $stubFacetFieldTransformation;

    final protected function setUp(): void
    {
        $this->registry = new FacetFieldTransformationRegistry;
        $this->stubFacetFieldTransformation = $this->createMock(FacetFieldTransformation::class);
    }

    public function testExceptionIsThrownDuringAttemptToRegisterTransformationWithNonStringCode(): void
    {
        $this->expectException(\TypeError::class);
        $this->registry->register(123, $this->stubFacetFieldTransformation);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     */
    public function testExceptionIsThrownDuringAttemptToRegisterTransformationWithInvalidCode(string $invalidCode): void
    {
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->register($invalidCode, $this->stubFacetFieldTransformation);
    }

    public function testExceptionIsThrownDuringAttemptToRetrieveTransformationByNonStringCode(): void
    {
        $this->expectException(\TypeError::class);
        $this->registry->getTransformationByCode(123);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     */
    public function testExceptionIsThrownDuringAttemptToRetrieveTransformationByInvalidCode(string $invalidCode): void
    {
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->getTransformationByCode($invalidCode);
    }

    public function testExceptionIsThrownDuringAttemptToCheckIfTransformationForNonStringCodeIsRegistered(): void
    {
        $this->expectException(\TypeError::class);
        $this->registry->hasTransformationForCode(123);
    }

    /**
     * @dataProvider invalidTransformationCodeDataProvider
     */
    public function testExceptionIsThrownDuringAttemptToCheckIfTransformationForInvalidCodeIsRegistered(
        string $invalidCode
    ) {
        $this->expectException(InvalidTransformationCodeException::class);
        $this->registry->hasTransformationForCode($invalidCode);
    }

    /**
     * @return array[]
     */
    public function invalidTransformationCodeDataProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testFalseIsReturnedIfNoTransformationWithGivenCodeIsRegistered(): void
    {
        $code = 'foo';
        $this->assertFalse($this->registry->hasTransformationForCode($code));
    }

    public function testTrueIsReturnedIfTransformationWithGivenCodeIsRegistered(): void
    {
        $code = 'foo';
        $this->registry->register($code, $this->stubFacetFieldTransformation);
        $this->assertTrue($this->registry->hasTransformationForCode($code));
    }

    public function testExceptionIsThrownIfNoTransformationWithGivenCodeIsRegistered(): void
    {
        $this->expectException(UnableToFindTransformationException::class);
        $code = 'foo';
        $this->assertSame($this->stubFacetFieldTransformation, $this->registry->getTransformationByCode($code));
    }

    public function testTransformationCanBeRetrievedByCode(): void
    {
        $code = 'foo';
        $this->registry->register($code, $this->stubFacetFieldTransformation);
        $this->assertSame($this->stubFacetFieldTransformation, $this->registry->getTransformationByCode($code));
    }
}
