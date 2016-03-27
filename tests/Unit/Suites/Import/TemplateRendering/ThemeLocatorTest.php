<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Layout;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Layout
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\LayoutReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Import\XPathParser
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
