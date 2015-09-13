<?php

namespace Brera\Renderer;

use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Renderer\ThemeLocator
 * @uses   \Brera\Renderer\Layout
 * @uses   \Brera\Renderer\LayoutReader
 * @uses   \Brera\Utils\XPathParser
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
    
    public function testHardcodedThemeDirectoryIsReturned()
    {
        $this->assertEquals('theme', $this->locator->getThemeDirectory());
    }

    public function testLayoutObjectIsReturnedForGivenHandle()
    {
        $layoutHandle = 'test_layout_handle_' . uniqid();
        $layoutFile = $this->locator->getThemeDirectory() . '/layout/' . $layoutHandle . '.xml';
        $this->createFixtureFile($layoutFile, '<layout></layout>');
        $result = $this->locator->getLayoutForHandle($layoutHandle);

        $this->assertInstanceOf(Layout::class, $result);
    }
}
