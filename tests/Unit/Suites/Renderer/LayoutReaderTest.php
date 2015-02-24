<?php

namespace Brera\Renderer;

/**
 * @covers Brera\Renderer\LayoutReader
 * @uses Brera\XPathParser
 * @uses Brera\Renderer\Layout
 */
class LayoutReaderTest extends \PHPUnit_Framework_TestCase
{
    use ThemeProductRenderingTestTrait;

    /**
     * @var LayoutReader
     */
    private $layoutReader;

    protected function setUp()
    {
        $this->layoutReader = new LayoutReader();

        $this->createTemporaryThemeFiles();
    }

    protected function tearDown()
    {
        $this->removeTemporaryThemeFiles();
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
        $filePath = $this->getLayoutDirectoryPath() . '/not-readable.xml';
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
        $layoutFile = $this->getLayoutDirectoryPath() . '/product_details_snippet.xml';
        $snippetLayout = $this->layoutReader->loadLayoutFromXmlFile($layoutFile);

        $topmostChildBlockLayoutArray = $snippetLayout->getNodeChildren();
        $topmostChildBlockLayout = array_shift($topmostChildBlockLayoutArray);
        $topmostChildBlockAttributes = $topmostChildBlockLayout->getAttributes();

        $expectedTemplatePath = $this->getTemplateDirectoryPath() . '/1column.phtml';

        $this->assertEquals($expectedTemplatePath, $topmostChildBlockAttributes['template']);
    }
}
