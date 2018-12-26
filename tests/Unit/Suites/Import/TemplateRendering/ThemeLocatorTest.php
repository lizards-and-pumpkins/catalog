<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Layout
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\LayoutXmlFileReader
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

    /**
     * @var MockObject|LayoutReader
     */
    private $layoutReaderMock;

    protected function setUp()
    {
        $this->preTestWorkingDirectory = getcwd();
        $testBasePath                  = realpath(sys_get_temp_dir());
        chdir($testBasePath . '/..');
        $this->layoutReaderMock = $this->createMock(LayoutReader::class);

        $this->locator = new ThemeLocator($testBasePath, $this->layoutReaderMock);

    }

    protected function tearDown()
    {
        chdir($this->preTestWorkingDirectory);
    }

    public function testPathToThemeDirectoryIsReturned()
    {
        $this->assertEquals(realpath(sys_get_temp_dir()) . '/theme', $this->locator->getThemeDirectory());
    }

    public function testLayoutObjectIsReturnedForGivenHandle()
    {
        $layoutHandle = 'test_layout_handle_' . uniqid('', true);
        $layoutFile = $this->locator->getThemeDirectory() . '/layout/' . $layoutHandle . '.xml';

        $this->layoutReaderMock->method('loadLayout')->with($layoutFile)->willReturn(
            $this->createMock(Layout::class)
        );

        $result = $this->locator->getLayoutForHandle($layoutHandle);

        $this->assertInstanceOf(Layout::class, $result);
    }
}
