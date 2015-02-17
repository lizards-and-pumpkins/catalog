<?php

namespace Brera;

use Brera\Context\Context;

/**
 * @covers \Brera\ThemeLocator
 */
class ThemeLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnHardcodedThemeDirectory()
    {
        $stubContext = $this->getMock(Context::class);

        $locator = new ThemeLocator();
        $result = $locator->getThemeDirectoryForContext($stubContext);

        $this->assertEquals('theme', $result);
    }
}
