<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\LayoutFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers LizardsAndPumpkins\Import\TemplateRendering\LayoutReader
 * @uses LizardsAndPumpkins\Import\XPathParser
 * @uses LizardsAndPumpkins\Import\TemplateRendering\Layout
 */
class LayoutReaderTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var LayoutReader
     */
    private $layoutReader;

    protected function setUp()
    {
        $this->layoutReader = new LayoutReader();
    }

    public function testExceptionIsThrownIfFileDesNotExist()
    {
        $this->expectException(LayoutFileNotReadableException::class);
        $this->layoutReader->loadLayoutFromXmlFile('some-non-existing-file-name.xml');
    }

    public function testExceptionIsThrownIfFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.xml';
        $this->createFixtureFile($filePath, '', 0000);
        $this->expectException(LayoutFileNotReadableException::class);

        $this->layoutReader->loadLayoutFromXmlFile($filePath);
    }

    public function testExceptionIsThrownIfPathIsADirectory()
    {
        $this->expectException(LayoutFileNotReadableException::class);
        $this->layoutReader->loadLayoutFromXmlFile(sys_get_temp_dir());
    }

    public function testLayoutIsReturned()
    {
        $layoutFile = $this->getUniqueTempDir() . '/test_layout.xml';
        $layoutXML = <<<EOX
<?xml version="1.0"?>
<snippet>
    <block name="test_block" class="LizardsAndPumpkins\Renderer\Block" template="some/template.phtml"/>
</snippet>
EOX;
        $this->createFixtureFile($layoutFile, $layoutXML);

        $snippetLayout = $this->layoutReader->loadLayoutFromXmlFile($layoutFile);
        $topmostChildBlockLayoutArray = $snippetLayout->getNodeChildren();
        $topmostChildBlockLayout = array_shift($topmostChildBlockLayoutArray);
        $topmostChildBlockAttributes = $topmostChildBlockLayout->getAttributes();

        $this->assertEquals('some/template.phtml', $topmostChildBlockAttributes['template']);
    }
}
