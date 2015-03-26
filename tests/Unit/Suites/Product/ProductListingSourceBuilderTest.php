<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingSourceBuilder
 * @uses   \Brera\XPathParser
 * @uses   \Brera\Product\ProductListingSource
 */
class ProductListingSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateAProductListingSourceFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru_de" language="en_US">
    <category>men-accessories</category>
</listing>
EOX;

        $builder = new ProductListingSourceBuilder();
        $productListingSource = $builder->createProductListingSourceFromXml($xml);

        $urlKey = $productListingSource->getUrlKey();
        $context = $this->getObjectProperty($productListingSource, 'contextData');
        $attributes = $this->getObjectProperty($productListingSource, 'criteria');

        $expectedUrlKey = 'men-accessories';
        $expectedContextData = ['website' => 'ru_de', 'language' => 'en_US'];
        $expectedCriteria = ['category' => 'men-accessories'];

        $this->assertInstanceOf(ProductListingSource::class, $productListingSource);
        $this->assertEquals($expectedUrlKey, $urlKey);
        $this->assertEquals($expectedContextData, $context);
        $this->assertEquals($expectedCriteria, $attributes);
    }

    /**
     * @test
     * @expectedException \Brera\Product\InvalidNumberOfUrlKeysPerImportedProductListingException
     * @expectedExceptionMessage There must be exactly one URL key in the imported product listing XML
     */
    public function itShouldThrowAnExceptionInCaseOfXmlHasNoEssentialData()
    {
        $xml = '<?xml version="1.0"?><node />';
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }

    /**
     * @param ProductListingSource $productSource
     * @param string $propertyName
     * @return mixed
     */
    private function getObjectProperty(ProductListingSource $productSource, $propertyName)
    {
        $property = new \ReflectionProperty($productSource, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($productSource);
    }
}
