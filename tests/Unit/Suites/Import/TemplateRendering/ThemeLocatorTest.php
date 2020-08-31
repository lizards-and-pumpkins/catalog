<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Layout
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\LayoutReader
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ThemeLocatorTest extends TestCase
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

    final protected function setUp(): void
    {
        $this->preTestWorkingDirectory = getcwd();
        $testBasePath = realpath(sys_get_temp_dir());
        chdir($testBasePath . '/..');
        $this->locator = new ThemeLocator($testBasePath);
    }

    final protected function tearDown(): void
    {
        chdir($this->preTestWorkingDirectory);
    }
    
    public function testPathToThemeDirectoryIsReturned(): void
    {
        $this->assertEquals(realpath(sys_get_temp_dir()) . '/theme', $this->locator->getThemeDirectory());
    }

    public function testLayoutObjectIsReturnedForGivenHandle(): void
    {
        $layoutHandle = 'test_layout_handle_' . uniqid();
        $layoutFile = $this->locator->getThemeDirectory() . '/layout/' . $layoutHandle . '.xml';
        $this->createFixtureFile($layoutFile, '<layout></layout>');
        $result = $this->locator->getLayoutForHandle($layoutHandle);

        $this->assertInstanceOf(Layout::class, $result);
    }
}
