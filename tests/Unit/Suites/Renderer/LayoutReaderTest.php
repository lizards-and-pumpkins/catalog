<?php

namespace Brera\Renderer;

/**
 * @covers Brera\Renderer\LayoutReader
 * @uses Brera\XPathParser
 * @uses Brera\Renderer\Layout
 */
class LayoutReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LayoutReader
     */
    private $layoutReader;

    protected function setUp()
    {
        $this->layoutReader = new LayoutReader();
    }

    protected function tearDown()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.xml';

        if (file_exists($filePath) && is_file($filePath)) {
            chmod($filePath, '600');
            unlink($filePath);
        }
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\LayoutFileNotReadableException
     */
    public function itShouldThrowExceptionIfFileDesNotExist()
    {
        $this->layoutReader->loadLayoutFromXmlFile('some-non-existing-file-name.xml');
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\LayoutFileNotReadableException
     */
    public function itShouldThrowExceptionIfFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.xml';

        touch($filePath);
        chmod($filePath, 000);

        $this->layoutReader->loadLayoutFromXmlFile($filePath);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\LayoutFileNotReadableException
     */
    public function itShouldThrowExceptionIfPathIsADirectory()
    {
        $this->layoutReader->loadLayoutFromXmlFile(sys_get_temp_dir());
    }

    /**
     * @test
     */
    public function itShouldReturnLayout()
    {
        $snippetLayout = $this->layoutReader->loadLayoutFromXmlFile('theme/layout/product_detail_view.xml');
        $topmostChildBlockLayoutArray = $snippetLayout->getNodeChildren();
        $topmostChildBlockLayout = array_shift($topmostChildBlockLayoutArray);
        $topmostChildBlockAttributes = $topmostChildBlockLayout->getAttributes();

        $this->assertEquals('theme/template/1column.phtml', $topmostChildBlockAttributes['template']);
    }
}
