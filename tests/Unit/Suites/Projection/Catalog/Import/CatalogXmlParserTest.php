<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceFilePathIsNotAStringException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXmlFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXmlFileIsNotReadableException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXMLNotAStringException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\CatalogXmlParser
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductImportCallbackFailureMessage
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageImportCallbackFailureMessage
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\CatalogListingImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class CatalogXmlParserTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @return string
     */
    private function getListingXml()
    {
        return <<<EOT
        <listing url_key="lizards" condition="and" website="test1" locale="xx_XX">
            <category operation="=">category-1</category>
            <brand operation="=">Lizards</brand>
        </listing>
EOT;
    }

    /**
     * @return string
     */
    private function getFirstImageXml()
    {
        return <<<EOT
                <image>
                    <file>first-image.jpg</file>
                    <label>The main image label</label>
                </image>
EOT;

    }

    /**
     * @return string
     */
    private function getSecondImageXml()
    {
        return <<<EOT
                <image>
                    <file>second-image.png</file>
                    <label locale="xx_XX">Second image label XX</label>
                    <label locale="yy_YY">Second image label YY</label>
                </image>
EOT;

    }

    /**
     * @param string|null $imageXml
     * @return string
     */
    private function getSimpleProductXml($imageXml = null)
    {
        $imageContent = isset($imageXml) ? $imageXml : ($this->getFirstImageXml() . $this->getSecondImageXml());
        return sprintf('
        <product type="simple" sku="test-sku">
            %s
            <attributes>
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
                <thing>Pumpkin</thing>
            </attributes>
        </product>
', $this->getImagesSectionWithContext($imageContent));
    }

    /**
     * @param string|null $imageXml
     * @return string
     */
    private function getInvalidSimpleProductXml($imageXml = null)
    {
        $imageContent = isset($imageXml) ? $imageXml : ($this->getFirstImageXml() . $this->getSecondImageXml());
        return sprintf('
        <product type="simple" sku="test-sku">
            %s
            <attributes>
                <category website="test1">category-1</category>
                <category locale="xx_XX">category-2</category>
            </attributes>
        </product>
', $this->getImagesSectionWithContext($imageContent));
    }

    /**
     * @param string $content
     * @return string
     */
    private function getProductSectionWithContent($content)
    {
        return sprintf('
    <products>
    %s
    </products>
', $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function getImagesSectionWithContext($content)
    {
        return sprintf('
    <images>
    %s
    </images>
', $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function getListingSectionWithContent($content)
    {
        return sprintf('
    <listings>
    %s
    </listings>
', $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function getCatalogXmlWithContent($content)
    {
        return sprintf(
            '<catalog  xmlns="http://lizardsandpumpkins.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    %s
</catalog>',
            $content
        );
    }

    /**
     * @return string
     */
    private function getCatalogXmlWithOneSimpleProduct()
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml()
            )
        );
    }

    /**
     * @return string
     */
    public function getCatalogXmlWithTwoSimpleProducts()
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml() .
                $this->getSimpleProductXml()
            )
        );
    }

    /**
     * @return string
     */
    private function getCatalogXmlWithTwoListings()
    {
        return $this->getCatalogXmlWithContent(
            $this->getListingSectionWithContent(
                $this->getListingXml() .
                $this->getListingXml()
            )
        );
    }

    /**
     * @return string
     */
    private function getCatalogXmlWithOneProductImage()
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml(
                    $this->getFirstImageXml()
                )
            )
        );
    }

    /**
     * @param string $filePath ∂
     * @param string $content
     */
    private function createFixtureFileAndPathWithContent($filePath, $content)
    {
        $this->createFixtureDirectory(dirname($filePath));
        $this->createFixtureFile($filePath, $content);
    }

    /**
     * @return string
     */
    private function createCatalogXmlFileWithOneSimpleProduct()
    {
        $filePath = $this->getUniqueTempDir() . '/simple-product.xml';
        $this->createFixtureFileAndPathWithContent($filePath, $this->getCatalogXmlWithOneSimpleProduct());
        return $filePath;
    }

    /**
     * @param string $expectedXml
     * @param int $expectedCallCount
     * @param string $callbackIdentifier
     * @return \Closure|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCallbackExpectingXml($expectedXml, $expectedCallCount, $callbackIdentifier)
    {
        $mockCallback = $this->getMock(Callback::class, ['__invoke']);
        $expected = new \DOMDocument();
        $expected->loadXML($expectedXml);
        $mockCallback->expects($this->exactly($expectedCallCount))->method('__invoke')->willReturnCallback(
            function ($xml) use ($expected, $callbackIdentifier) {
                $actual = new \DOMDocument();
                $actual->loadXML($xml);
                $message = sprintf('The argument XML for the callback "%s" did not match', $callbackIdentifier);
                $this->assertEqualXMLStructure($expected->firstChild, $actual->firstChild, false, $message);
            }
        );
        return $mockCallback;
    }

    protected function setUp()
    {
        $this->mockLogger = $this->getMock(Logger::class);
    }

    /**
     * @param int|object|null $invalidSourceFilePath
     * @param string $expectedType
     * @dataProvider invalidSourceFilePathDataProvider
     */
    public function testItThrowsAnExceptionIfTheFromFileConstructorInputIsNotAString(
        $invalidSourceFilePath,
        $expectedType
    ) {
        $this->setExpectedException(
            CatalogImportSourceFilePathIsNotAStringException::class,
            sprintf('Expected the catalog XML import file path to be a string, got "%s"', $expectedType)
        );
        CatalogXmlParser::fromFilePath($invalidSourceFilePath, $this->mockLogger);
    }

    /**
     * @param int|object|null $noXmlStringInput
     * @param string $expectedType
     * @dataProvider invalidSourceFilePathDataProvider
     */
    public function testItThrowsAnExceptionIfTheFromXmlConstructorInputIsNotAString(
        $noXmlStringInput,
        $expectedType
    ) {
        $this->setExpectedException(
            CatalogImportSourceXMLNotAStringException::class,
            sprintf('Expected the catalog XML to be a string, got "%s"', $expectedType)
        );
        CatalogXmlParser::fromXml($noXmlStringInput, $this->mockLogger);
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
            CatalogImportSourceXmlFileDoesNotExistException::class,
            sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
        );
        CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
    }

    public function testItThrowsAnExceptionIfTheInputFileIsNotReadable()
    {
        $dirPath = $this->getUniqueTempDir();
        $sourceFilePath = $dirPath . '/not-readable.xml';
        $this->createFixtureDirectory($dirPath);
        $this->createFixtureFile($sourceFilePath, '', 0000);

        $this->setExpectedException(
            CatalogImportSourceXmlFileIsNotReadableException::class,
            sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
        );
        CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAFile()
    {
        $sourceFilePath = $this->createCatalogXmlFileWithOneSimpleProduct();
        $instance = CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAXmlString()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct(), $this->mockLogger);
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItCallsAllRegisteredProductBuilderCallbacksForOneProduct()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct(), $this->mockLogger);
        $expectedXml = $this->getSimpleProductXml();
        $callCount = 1;
        $productCallbackA = $this->createMockCallbackExpectingXml($expectedXml, $callCount, 'productCallbackA');
        $productCallbackB = $this->createMockCallbackExpectingXml($expectedXml, $callCount, 'productCallbackB');
        $instance->registerProductCallback($productCallbackA);
        $instance->registerProductCallback($productCallbackB);
        $instance->parse();
    }

    public function testItCallsRegisteredProductBuilderCallbackForTwoProducts()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoSimpleProducts(), $this->mockLogger);
        $expectedXml = $this->getSimpleProductXml();
        $callCount = 2;
        $callback = $this->createMockCallbackExpectingXml($expectedXml, $callCount, 'productCallback');
        $instance->registerProductCallback($callback);
        $instance->parse();
    }

    public function testItCallsAllRegisteredListingCallbacks()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoListings(), $this->mockLogger);
        $expectedXml = $this->getListingXml();
        $expectedCallCount = 2;
        $callback = $this->createMockCallbackExpectingXml($expectedXml, $expectedCallCount, 'imageCallback');
        $instance->registerListingCallback($callback);
        $instance->parse();
    }

    public function testItCallsAllRegisteredImageCallbacks()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneProductImage(), $this->mockLogger);
        $callCount = 1;
        $expectedXml = $this->getFirstImageXml();
        $callback = $this->createMockCallbackExpectingXml($expectedXml, $callCount, 'imageCallback');
        $instance->registerProductImageCallback($callback);
        $instance->parse();
    }

    public function testItDoesNotCallImageCallbacksForAProductIfProductCallbackThrowsException()
    {
        $imageXml = $this->getFirstImageXml();
        $invalidProductXml = $this->getInvalidSimpleProductXml($imageXml);
        $invalidCatalogXml = $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent($invalidProductXml)
        );
        
        $this->mockLogger->expects($this->once())->method('log')
            ->with($this->isInstanceOf(ProductImportCallbackFailureMessage::class));
        
        $instance = CatalogXmlParser::fromXml($invalidCatalogXml, $this->mockLogger);
        
        $productCallback = $this->getMock(Callback::class, ['__invoke']);
        $productCallback->expects($this->once())->method('__invoke')->willThrowException(new \Exception('Test dummy'));
        $instance->registerProductCallback($productCallback);
        
        $expectedImageCalls = 0;
        $imageCallback = $this->createMockCallbackExpectingXml($imageXml, $expectedImageCalls, 'imageCallback');
        $instance->registerProductImageCallback($imageCallback);
        $instance->parse();
    }

    public function testItLogsAnExceptionsWhileProcessingImageCallbacks()
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->with($this->isInstanceOf(ProductImageImportCallbackFailureMessage::class));

        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct(), $this->mockLogger);

        $imageCallback = $this->getMock(Callback::class, ['__invoke']);
        $imageCallback->expects($this->once())->method('__invoke')->willThrowException(new \Exception('Test dummy'));
        $instance->registerProductImageCallback($imageCallback);
        $instance->parse();
    }

    public function testItLogsAnExceptionsWhileProcessingListingCallbacks()
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->with($this->isInstanceOf(CatalogListingImportCallbackFailureMessage::class));

        $xml = $this->getCatalogXmlWithContent(
            $this->getListingSectionWithContent(
                $this->getListingXml()
            )
        );
        $instance = CatalogXmlParser::fromXml($xml, $this->mockLogger);
        
        $listingCallback = $this->getMock(Callback::class, ['__invoke']);
        $listingCallback->expects($this->once())->method('__invoke')->willThrowException(new \Exception('Test dummy'));

        $instance->registerListingCallback($listingCallback);
        $instance->parse();
    }
}
