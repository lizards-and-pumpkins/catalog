<?php

namespace Brera;

use Brera\Renderer\Layout;

/**
 * @covers \Brera\ThemeLocator
 * @uses   \Brera\Renderer\Layout
 * @uses   \Brera\Renderer\LayoutReader
 * @uses   \Brera\XPathParser
 */
class ThemeLocatorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var ThemeLocator
     */
    private $locator;
    
    protected function setUp()
    {
        $this->locator = new ThemeLocator();
    }
    
    /**
     * @test
     */
    public function itShouldReturnHardcodedThemeDirectory()
    {
        $this->assertEquals('theme', $this->locator->getThemeDirectory());
    }

    /**
     * @test
     */
    public function itShouldReturnALayoutObjectforAGivenHandle()
    {
        $layoutHandle = 'test_layout_handle_' . uniqid();
        $layoutFile = $this->locator->getThemeDirectory() . '/layout/' . $layoutHandle . '.xml';
        $this->createFixtureFile($layoutFile, '<layout></layout>');
        $result = $this->locator->getLayoutForHandle($layoutHandle);
        $this->assertInstanceOf(Layout::class, $result);
    }
}
