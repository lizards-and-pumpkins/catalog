<?php

namespace Brera;

/**
 * @covers Brera\LayoutReader
 * @uses Brera\XPathParser
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

    /**
     * @test
     * @expectedException \Brera\LayoutFileNotReadableException
     */
    public function itShouldThrowExceptionIfFileDesNotExist()
    {
        $this->layoutReader->loadLayoutFromXmlFile('some-non-existing-file-name.xml');
    }

    /**
     * @test
     * @expectedException \Brera\LayoutFileNotReadableException
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
     * @expectedException \Brera\LayoutFileNotReadableException
     */
    public function itShouldThrowExceptionIfPathIsADirectory()
    {
        $this->layoutReader->loadLayoutFromXmlFile(sys_get_temp_dir());
    }

    /**
     * @test
     */
    public function itShouldReturnLayoutAsAnArray()
    {
        $layout = $this->layoutReader->loadLayoutFromXmlFile('theme/layout/product_details_page.xml');

        $this->assertEquals('1column.phtml', $layout[0]['value'][0]['attributes']['template']);
    }
}
