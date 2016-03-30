<?php

namespace LizardsAndPumpkins\Util;

/**
 * @covers \LizardsAndPumpkins\Util\UuidGenerator
 */
class UuidGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsAString()
    {
        $this->assertInternalType('string', UuidGenerator::getUuid());
    }

    public function testItReturnsTwoDifferentStringsOnSubsequentCalls()
    {
        $this->assertNotSame(UuidGenerator::getUuid(), UuidGenerator::getUuid());
    }

    public function testItReturnsAStringMatchingRFC4122()
    {
        //Example: 9d0599d4-e8fa-478d-a559-936a5cc20c1d
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/';
        $this->assertRegExp($pattern, UuidGenerator::getUuid());
    }
}
