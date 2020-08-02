<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\XmlParser;

use LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileDoesNotExistException;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileIsNotReadableException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml;

/**
 * @covers \LizardsAndPumpkins\Import\XmlParser\CatalogXmlParser
 * @covers \LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage
 * @covers \LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class CatalogXmlParserTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Logger|MockObject
     */
    private $mockLogger;

    private function getListingXml() : string
    {
        return <<<EOT
        <listing url_key="lizards" condition="and" website="test1" locale="xx_XX">
            <category operation="=">category-1</category>
            <brand operation="=">Lizards</brand>
        </listing>
EOT;
    }

    private function getFirstImageXml() : string
    {
        return <<<EOT
                <image>
                    <file>first-image.jpg</file>
                    <label>The main image label</label>
                </image>
EOT;

    }

    private function getSecondImageXml() : string
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
    private function getSimpleProductXml($imageXml = null) : string
    {
        $imageContent = isset($imageXml) ? $imageXml : ($this->getFirstImageXml() . $this->getSecondImageXml());
        return sprintf('
        <product type="simple" sku="test-sku">
            %s
            <attributes>
                <attribute name="category" website="test1" locale="xx_XX">category-1</attribute>
                <attribute name="category" website="test2" locale="xx_XX">category-1</attribute>
                <attribute name="category" website="test2" locale="yy_YY">category-1</attribute>
                <attribute name="stock_qty">111</attribute>
                <attribute name="backorders">true</attribute>
                <attribute name="url_key" locale="xx_XX">xx-url-key</attribute>
                <attribute name="url_key" locale="yy_YY">yy-url-key</attribute>
                <attribute name="name">Test Product Definition</attribute>
                <attribute name="price" website="test1">9.99</attribute>
                <attribute name="price" website="test2">7.99</attribute>
                <attribute name="special_price" website="test2">5.99</attribute>
                <attribute name="description"><![CDATA[A Description with some <strong>Tags</strong>]]></attribute>
                <attribute name="brand">Lizards</attribute>
                <attribute name="thing">Pumpkin</attribute>
            </attributes>
        </product>
', $this->getImagesSectionWithContext($imageContent));
    }

    private function getProductSectionWithContent(string $content) : string
    {
        return sprintf('
    <products>
    %s
    </products>
', $content);
    }

    private function getImagesSectionWithContext(string $content) : string
    {
        return sprintf('
    <images>
    %s
    </images>
', $content);
    }

    private function getListingSectionWithContent(string $content) : string
    {
        return sprintf('
    <listings>
    %s
    </listings>
', $content);
    }

    private function getCatalogXmlWithContent(string $content) : string
    {
        return sprintf(
            '<catalog  xmlns="http://lizardsandpumpkins.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    %s
</catalog>',
            $content
        );
    }

    private function getCatalogXmlWithOneSimpleProduct() : string
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml()
            )
        );
    }

    public function getCatalogXmlWithTwoSimpleProducts() : string
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml() .
                $this->getSimpleProductXml()
            )
        );
    }

    private function getCatalogXmlWithTwoListings() : string
    {
        return $this->getCatalogXmlWithContent(
            $this->getListingSectionWithContent(
                $this->getListingXml() .
                $this->getListingXml()
            )
        );
    }

    private function createFixtureFileAndPathWithContent(string $filePath, string $content): void
    {
        $this->createFixtureDirectory(dirname($filePath));
        $this->createFixtureFile($filePath, $content);
    }

    private function createCatalogXmlFileWithOneSimpleProduct() : string
    {
        $filePath = $this->getUniqueTempDir() . '/simple-product.xml';
        $this->createFixtureFileAndPathWithContent($filePath, $this->getCatalogXmlWithOneSimpleProduct());
        return $filePath;
    }

    /**
     * @param string $expectedXml
     * @param int $expectedCallCount
     * @param string $callbackIdentifier
     * @return callable|MockObject
     */
    private function createMockCallbackExpectingXml(
        string $expectedXml,
        int $expectedCallCount,
        string $callbackIdentifier
    ) : callable {
        $mockCallback = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $expected = new \DOMDocument();
        $expected->loadXML($expectedXml);
        $mockCallback->expects($this->exactly($expectedCallCount))->method('__invoke')->willReturnCallback(
            function ($xml) use ($expected, $callbackIdentifier) {
                $actual = new \DOMDocument();
                $actual->loadXML($xml);
                $message = sprintf('The argument XML for the callback "%s" did not match', $callbackIdentifier);
                $this->assertEqualStructure($expected->firstChild, $actual->firstChild, $message);
            }
        );
        return $mockCallback;
    }

    private function assertEqualStructure(\DOMElement $expectedElement, \DOMElement $actualElement, string $message): void
    {
        $expectedElement = Xml::import($expectedElement);
        $actualElement = Xml::import($actualElement);

        static::assertSame($expectedElement->tagName, $actualElement->tagName, $message);

        Xml::removeCharacterDataNodes($expectedElement);
        Xml::removeCharacterDataNodes($actualElement);

        static::assertSame($expectedElement->childNodes->length, $actualElement->childNodes->length, sprintf(
            '%s%sNumber of child nodes of "%s" differs',
            $message,
            !empty($message) ? "\n" : '',
            $expectedElement->tagName
        ));

        for ($i = 0; $i < $expectedElement->childNodes->length; $i++) {
            static::assertEqualStructure(
                $expectedElement->childNodes->item($i),
                $actualElement->childNodes->item($i),
                $message
            );
        }
    }

    final protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(Logger::class);
    }

    public function testItThrowsAnExceptionIfTheFromFileConstructorInputIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        CatalogXmlParser::fromFilePath(123, $this->mockLogger);
    }

    public function testItThrowsAnExceptionIfTheFromXmlConstructorInputIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        CatalogXmlParser::fromXml([], $this->mockLogger);
    }

    public function testItThrowsAnExceptionIfTheInputFileDoesNotExist(): void
    {
        $sourceFilePath = 'non-existent-file.xml';
        $this->expectException(CatalogImportSourceXmlFileDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath));
        CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
    }

    public function testItThrowsAnExceptionIfTheInputFileIsNotReadable(): void
    {
        $dirPath = $this->getUniqueTempDir();
        $sourceFilePath = $dirPath . '/not-readable.xml';
        $this->createFixtureDirectory($dirPath);
        $this->createFixtureFile($sourceFilePath, '', 0000);

        $this->expectException(CatalogImportSourceXmlFileIsNotReadableException::class);
        $this->expectExceptionMessage(sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath));
        CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAFile(): void
    {
        $sourceFilePath = $this->createCatalogXmlFileWithOneSimpleProduct();
        $instance = CatalogXmlParser::fromFilePath($sourceFilePath, $this->mockLogger);
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAXmlString(): void
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct(), $this->mockLogger);
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItCallsAllRegisteredProductBuilderCallbacksForOneProduct(): void
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

    public function testItCallsRegisteredProductBuilderCallbackForTwoProducts(): void
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoSimpleProducts(), $this->mockLogger);
        $expectedXml = $this->getSimpleProductXml();
        $callCount = 2;
        $callback = $this->createMockCallbackExpectingXml($expectedXml, $callCount, 'productCallback');
        $instance->registerProductCallback($callback);
        $instance->parse();
    }

    public function testItCallsAllRegisteredListingCallbacks(): void
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoListings(), $this->mockLogger);
        $expectedXml = $this->getListingXml();
        $expectedCallCount = 2;
        $callback = $this->createMockCallbackExpectingXml($expectedXml, $expectedCallCount, 'imageCallback');
        $instance->registerListingCallback($callback);
        $instance->parse();
    }
    
    public function testItLogsAnExceptionsWhileProcessingListingCallbacks(): void
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->with($this->isInstanceOf(CatalogListingImportCallbackFailureMessage::class));

        $xml = $this->getCatalogXmlWithContent(
            $this->getListingSectionWithContent(
                $this->getListingXml()
            )
        );
        $instance = CatalogXmlParser::fromXml($xml, $this->mockLogger);

        /** @var callable|MockObject $listingCallback */
        $listingCallback = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $listingCallback->expects($this->once())->method('__invoke')->willThrowException(new \Exception('Test dummy'));

        $instance->registerListingCallback($listingCallback);
        $instance->parse();
    }
}
