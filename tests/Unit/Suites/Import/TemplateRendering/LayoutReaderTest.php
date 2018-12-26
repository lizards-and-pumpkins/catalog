<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\LayoutFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\LayoutXmlFileReader
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Layout
 */
class LayoutReaderTest extends TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var LayoutXmlFileReader
     */
    private $layoutReader;

    protected function setUp()
    {
        $this->layoutReader = new LayoutXmlFileReader();
    }

    public function testExceptionIsThrownIfFileDesNotExist()
    {
        $this->expectException(LayoutFileNotReadableException::class);
        $this->layoutReader->loadLayout('some-non-existing-file-name.xml');
    }

    public function testExceptionIsThrownIfFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.xml';
        $this->createFixtureFile($filePath, '', 0000);
        $this->expectException(LayoutFileNotReadableException::class);

        $this->layoutReader->loadLayout($filePath);
    }

    public function testExceptionIsThrownIfPathIsADirectory()
    {
        $this->expectException(LayoutFileNotReadableException::class);
        $this->layoutReader->loadLayout(sys_get_temp_dir());
    }

    public function testLayoutIsReturned()
    {
        $layoutFile = $this->getUniqueTempDir() . '/test_layout.xml';
        $layoutXML = '<?xml version="1.0"?><snippet><block name="foo" class="Bar\Baz" template="qux.phtml"/></snippet>';
        $this->createFixtureFile($layoutFile, $layoutXML);

        $snippetLayout = $this->layoutReader->loadLayout($layoutFile);
        $topmostChildBlockLayoutArray = $snippetLayout->getNodeChildren();
        
        /** @var Layout $topmostChildBlockLayout */
        $topmostChildBlockLayout = array_shift($topmostChildBlockLayoutArray);
        $topmostChildBlockAttributes = $topmostChildBlockLayout->getAttributes();

        $this->assertEquals('qux.phtml', $topmostChildBlockAttributes['template']);
    }
}
