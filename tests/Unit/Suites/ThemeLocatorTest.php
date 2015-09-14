<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Renderer\Layout;

/**
 * @covers \LizardsAndPumpkins\ThemeLocator
 * @uses   \LizardsAndPumpkins\Renderer\Layout
 * @uses   \LizardsAndPumpkins\Renderer\LayoutReader
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
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
