<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Product\ProductImageList;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\Tax\ProductTaxClass;

class ProductRehydrationTest extends \PHPUnit_Framework_TestCase
{
    private function assertBasicProductPropertyEqual(Product $sourceProduct, Product $rehydratedProduct)
    {
        $this->assertEquals($sourceProduct->getId(), $rehydratedProduct->getId());
        $this->assertSame(
            $sourceProduct->getAllValuesOfAttribute('foo'),
            $rehydratedProduct->getAllValuesOfAttribute('foo')
        );
        $this->assertSame($sourceProduct->getImageCount(), $rehydratedProduct->getImageCount());
    }

    private function assertConfigurableProductEquals(
        ConfigurableProduct $sourceConfigurableProduct,
        ConfigurableProduct $rehydratedConfigurableProduct
    ) {
        $this->assertBasicProductPropertyEqual($sourceConfigurableProduct, $rehydratedConfigurableProduct);

        $this->assertVariationAttributeListEquals(
            $sourceConfigurableProduct->getVariationAttributes(),
            $rehydratedConfigurableProduct->getVariationAttributes()
        );

        $this->assertAssociatedProductListEquals(
            $sourceConfigurableProduct->getAssociatedProducts(),
            $rehydratedConfigurableProduct->getAssociatedProducts()
        );
    }

    private function assertProductEquals(Product $sourceProduct, Product $rehydratedProduct)
    {
        if ($sourceProduct instanceof SimpleProduct) {
            $this->assertSimpleProductEquals($sourceProduct, $rehydratedProduct);
        } elseif ($sourceProduct instanceof ConfigurableProduct) {
            $this->assertConfigurableProductEquals($sourceProduct, $rehydratedProduct);
        } else {
            $this->fail(sprintf('Unable to assert equality on unknown product class "%s"', get_class($sourceProduct)));
        }
    }

    private function assertSimpleProductEquals(
        SimpleProduct $sourceSimpleProduct,
        SimpleProduct $rehydratedSimpleProduct
    ) {
        $this->assertBasicProductPropertyEqual($sourceSimpleProduct, $rehydratedSimpleProduct);
    }

    private function assertVariationAttributeListEquals(
        ProductVariationAttributeList $sourceVariationAttributeList,
        ProductVariationAttributeList $rehydratedVariationAttributeList
    ) {
        $this->assertSame(
            count($sourceVariationAttributeList),
            count($rehydratedVariationAttributeList)
        );
        
        foreach ($sourceVariationAttributeList as $idx => $attribute) {
            $this->assertTrue($attribute->isEqualTo($rehydratedVariationAttributeList->getAttributes()[$idx]));
        }
    }

    private function assertAssociatedProductListEquals(
        AssociatedProductList $sourceAssociatedProductList,
        AssociatedProductList $rehydratedAssociatedProductList
    ) {
        $this->assertSame(
            count($sourceAssociatedProductList),
            count($rehydratedAssociatedProductList)
        );
        
        array_map(function ($idx) use ($sourceAssociatedProductList, $rehydratedAssociatedProductList) {
            $sourceAssociatedProduct = $sourceAssociatedProductList->getProducts()[$idx];
            $rehydratedAssociatedProduct = $rehydratedAssociatedProductList->getProducts()[$idx];
            $this->assertProductEquals($sourceAssociatedProduct, $rehydratedAssociatedProduct);
        }, array_keys($sourceAssociatedProductList->getProducts()));
    }

    /**
     * @param string $productIdString
     * @return SimpleProduct
     */
    private function createSimpleProductWithId($productIdString)
    {
        $productId = ProductId::fromString($productIdString);
        
        $productTaxClass = ProductTaxClass::fromString('test');
        
        $testProductAttribute = $this->createProductAttribute('foo', uniqid());
        $testProductAttributes = new ProductAttributeList($testProductAttribute);

        $imageFileAttribute = $this->createProductAttribute(ProductImage::FILE, 'test.jpg');
        $imageLabelAttribute = $this->createProductAttribute(ProductImage::LABEL, 'Product Label');
        
        $image = new ProductImage(new ProductAttributeList($imageFileAttribute, $imageLabelAttribute));
        $imageList = new ProductImageList($image);

        $stubContext = $this->getMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([ContextVersion::CODE => '123']);

        return new SimpleProduct($productId, $productTaxClass, $testProductAttributes, $imageList, $stubContext);
    }

    /**
     * @param string $code
     * @param string $value
     * @return ProductAttribute
     */
    private function createProductAttribute($code, $value)
    {
        $contextData = [];
        return new ProductAttribute(AttributeCode::fromString($code), $value, $contextData);
    }

    /**
     * @param string $idString
     * @param Product[] $childProducts
     * @return ConfigurableProduct
     */
    private function createConfigurableProductWithIdAndAssociatedProducts($idString, Product ...$childProducts)
    {
        $simpleProduct = $this->createSimpleProductWithId($idString);

        $variationAttributes = new ProductVariationAttributeList(AttributeCode::fromString('foo'));
        $associatedProducts = new AssociatedProductList(...$childProducts);
        return new ConfigurableProduct($simpleProduct, $variationAttributes, $associatedProducts);
    }

    public function testASimpleProductCanBeJsonSerializedAndRehydrated()
    {
        $sourceSimpleProduct = $this->createSimpleProductWithId('test');
        $json = json_encode($sourceSimpleProduct);
        $rehydratedSimpleProduct = SimpleProduct::fromArray(json_decode($json, true));

        $this->assertSimpleProductEquals($sourceSimpleProduct, $rehydratedSimpleProduct);
    }

    public function testAConfigurableProductCanBeJsonSerializedAndRehydrated()
    {
        $sourceConfigurableProduct = $this->createConfigurableProductWithIdAndAssociatedProducts(
            'root',
            $this->createConfigurableProductWithIdAndAssociatedProducts(
                'config1',
                $this->createSimpleProductWithId('config1_simple1'),
                $this->createSimpleProductWithId('config1_simple2')
            ),
            $this->createSimpleProductWithId('root_simple1'),
            $this->createSimpleProductWithId('root_simple2'),
            $this->createSimpleProductWithId('root_simple3')
        );
        $json = json_encode($sourceConfigurableProduct);
        $rehydratedConfigurableProduct = ConfigurableProduct::fromArray(json_decode($json, true));

        $this->assertConfigurableProductEquals($sourceConfigurableProduct, $rehydratedConfigurableProduct);
    }
}
