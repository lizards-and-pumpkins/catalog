<?php

namespace Brera;

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
        $stubEnvironment = $this->getMock(Environment::class);

        $locator = new ThemeLocator();
        $result = $locator->getThemeDirectoryForEnvironment($stubEnvironment);

        $this->assertEquals('theme', $result);
    }
}
