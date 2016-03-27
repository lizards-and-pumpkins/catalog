<?php

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\InvalidTemplateProjectorCodeException;
use LizardsAndPumpkins\Import\RootTemplate\Exception\UnableToLocateTemplateProjectorException;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 */
class TemplateProjectorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateProjectorLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new TemplateProjectorLocator;
    }

    public function testExceptionIsThrownIfNonStringCodeIsPassed()
    {
        $this->expectException(InvalidTemplateProjectorCodeException::class);
        $this->locator->getTemplateProjectorForCode(1);
    }

    public function testExceptionIsThrownIfProjectorCanNotBeLocated()
    {
        $this->expectException(UnableToLocateTemplateProjectorException::class);
        $this->locator->getTemplateProjectorForCode('foo');
    }

    public function testExceptionIsThrownDuringAttemptToRegisterProjectorWithNonStringCode()
    {
        $invalidTemplateCode = 1;
        $this->expectException(InvalidTemplateProjectorCodeException::class);

        $this->locator->register($invalidTemplateCode, $this->getStubProjector());
    }

    public function testProjectorForTemplateCodesIsReturned()
    {
        $dummyTemplateCode = 'foo';

        $stubProjector = $this->getStubProjector();
        $this->locator->register($dummyTemplateCode, $stubProjector);
        $result = $this->locator->getTemplateProjectorForCode($dummyTemplateCode);

        $this->assertSame($stubProjector, $result);
    }

    public function testSameInstanceForSameTemplateCodeIsReturned()
    {
        $dummyTemplateCode = 'foo';

        $this->locator->register($dummyTemplateCode, $this->getStubProjector());
        $resultA = $this->locator->getTemplateProjectorForCode($dummyTemplateCode);
        $resultB = $this->locator->getTemplateProjectorForCode($dummyTemplateCode);

        $this->assertSame($resultA, $resultB);
    }

    public function testDifferentInstancesAreReturnedForDifferentTemplateCodes()
    {
        $dummyTemplateCodeA = 'foo';
        $stubProjectorA = $this->getStubProjector();
        $this->locator->register($dummyTemplateCodeA, $stubProjectorA);

        $dummyTemplateCodeB = 'test2';
        $stubProjectorB = $this->getStubProjector();
        $this->locator->register($dummyTemplateCodeB, $stubProjectorB);

        $resultA = $this->locator->getTemplateProjectorForCode($dummyTemplateCodeA);
        $resultB = $this->locator->getTemplateProjectorForCode($dummyTemplateCodeB);

        $this->assertNotSame($resultA, $resultB);
    }

    /**
     * @return Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProjector()
    {
        return $this->getMock(Projector::class);
    }
}
