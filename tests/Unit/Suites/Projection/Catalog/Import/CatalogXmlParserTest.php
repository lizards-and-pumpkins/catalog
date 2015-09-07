<?php


namespace Brera\Projection\Catalog\Import;

use Brera\Product\ProductSource;
use Brera\TestFileFixtureTrait;

class CatalogXmlParserTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @return string
     */
    private function getListingsXml()
    {
        return <<<EOT
    <listings>
        <listing url_key="lizards" condition="and" website="test1" locale="xx_XX">
            <category operation="=">category-1</category>
            <brand operation="=">Lizards</brand>
        </listing>
        <listing url_key="lizards" condition="and" website="test2" locale="xx_XX">
            <category operation="=">category-2</category>
        </listing>
    </listings>

EOT;
    }

    /**
     * @return string
     */
    private function getSimpleProductXml()
    {
        return <<<EOT
        <product type="simple" sku="test-sku" visible="true" tax_class_id="123">
            <attributes>
                <image>
                    <main>true</main>
                    <file>first-image.jpg</file>
                    <label>The main image label</label>
                </image>
                <image>
                    <show_in_gallery>false</show_in_gallery>
                    <file>second-image.png</file>
                    <label locale="xx_XX">Second image label XX</label>
                    <label locale="yy_YY">Second image label YY</label>
                </image>
                <category website="test1" locale="xx_XX">category-1</category>
                <category website="test2" locale="xx_XX">category-1</category>
                <category website="test2" locale="yy_YY">category-1</category>
                <stock_qty>111</stock_qty>
                <backorders>true</backorders>
                <url_key locale="xx_XX">xx-url-key</url_key>
                <url_key locale="yy_YY">yy-url-key</url_key>
                <name>Test Product Definition</name>
                <price website="test1">9.99</price>
                <price website="test2">7.99</price>
                <special_price website="test2">5.99</special_price>
                <description><![CDATA[A Description with some <strong>Tags</strong>]]></description>
                <brand>Lizards</brand>
                <style>Pumpkin</style>
            </attributes>
        </product>
EOT;
    }

    /**
     * @return string
     */
    private function getSimpleProductInputFilePath()
    {
        $dirPath = $this->getUniqueTempDir();
        $filePath = $dirPath . '/simple-product.xml';
        $contents = sprintf(
            '<catalog  xmlns="http://brera.io" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <products>
    %s
    </products>
</catalog>', $this->getSimpleProductXml()
        );
        $this->createFixtureDirectory($dirPath);
        $this->createFixtureFile($filePath, $contents);
        return $filePath;
    }

    /**
     * @param string $className
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCallbackExpectingClass($className)
    {
        $mockCallback = $this->getMock(Callback::class, ['__invoke']);
        $mockCallback->expects($this->once())->method('__invoke')->with($this->isInstanceOf($className));
        return $mockCallback;
    }

    /**
     * @param int|object|null $invalidSourceFilePath
     * @param string $expectedType
     * @dataProvider invalidSourceFilePathDataProvider
     */
    public function testItThrowsAnExceptionIfTheNamedConstructorInputIsNotAString($invalidSourceFilePath, $expectedType)
    {
        $this->setExpectedException(
            Exception\CatalogImportSourceFilePathIsNotAStringException::class,
            sprintf('Expected the catalog XML import file path to be a string, got "%s"', $expectedType)
        );
        CatalogXmlParser::forFile($invalidSourceFilePath);
    }

    /**
     * @return array[]
     */
    public function invalidSourceFilePathDataProvider()
    {
        return [
            [null, 'NULL'],
            [42, 'integer'],
            [new \stdClass(), 'stdClass'],
        ];
    }

    public function testItThrowsAnExceptionIfTheInputFileDoesNotExist()
    {
        $sourceFilePath = 'non-existent-file.xml';
        $this->setExpectedException(
            Exception\CatalogImportSourceXmlFileDoesNotExistException::class,
            sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
        );
        CatalogXmlParser::forFile($sourceFilePath);
    }

    public function testItThrowsAnExceptionIfTheInputFileIsNotReadable()
    {
        $dirPath = $this->getUniqueTempDir();
        $sourceFilePath = $dirPath . '/not-readable.xml';
        $this->createFixtureDirectory($dirPath);
        $this->createFixtureFile($sourceFilePath, '', 0000);
        
        $this->setExpectedException(
            Exception\CatalogImportSourceXmlFileIsNotReadableException::class,
            sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
        );
        CatalogXmlParser::forFile($sourceFilePath);
    }

    public function testItCallsRegisteredProductSourceCallbacks()
    {
        $sourceFilePath = $this->getSimpleProductInputFilePath();
        $parser = CatalogXmlParser::forFile($sourceFilePath);
        $mockCallback = $this->createMockCallbackExpectingClass(ProductSource::class);
        $parser->registerCallbackForProductSource($mockCallback);
        $parser->parse();
    }
}
