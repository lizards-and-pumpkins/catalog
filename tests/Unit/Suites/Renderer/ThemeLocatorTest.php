<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Renderer\ThemeLocator
 * @uses   \LizardsAndPumpkins\Renderer\Layout
 * @uses   \LizardsAndPumpkins\Renderer\LayoutReader
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class ThemeLocatorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var ThemeLocator
     */
    private $locator;

    /**
     * @var string
     */
    private $preTestWorkingDirectory;

    protected function setUp()
    {
        $this->preTestWorkingDirectory = getcwd();
        $testBasePath = realpath(sys_get_temp_dir());
        chdir($testBasePath . '/..');
        $this->locator = ThemeLocator::fromPath($testBasePath);
    }

    protected function tearDown()
    {
        chdir($this->preTestWorkingDirectory);
    }
    
    public function testRelativePathToHardcodedThemeDirectoryIsReturned()
    {
        $this->assertEquals(basename(sys_get_temp_dir()) . '/theme', $this->locator->getThemeDirectory());
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
