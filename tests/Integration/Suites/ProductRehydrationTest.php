<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\VersionedContext;
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

class ProductRehydrationTest extends \PHPUnit_Framework_TestCase
{
    private function assertBasicProductPropertyEqual(Product $sourceProduct, Product $rehydratedProduct)
    {
        $this->assertSame(
            (string) $sourceProduct->getId(),
            (string) $rehydratedProduct->getId()
        );
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
        } else {
            $this->assertConfigurableProductEquals($sourceProduct, $rehydratedProduct);
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
        /**
         * @var Product $sourceAssociatedProduct
         */
        foreach ($sourceAssociatedProductList as $idx => $sourceAssociatedProduct) {
            $this->assertProductEquals(
                $sourceAssociatedProduct,
                $rehydratedAssociatedProductList->getProducts()[$idx]
            );
        }
    }

    /**
     * @param string $productIdString
     * @return SimpleProduct
     */
    private function createSimpleProductWithId($productIdString)
    {
        $productId = ProductId::fromString($productIdString);
        $testProductAttributes = ProductAttribute::fromArray(
            [
                ProductAttribute::CODE => 'foo',
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => uniqid()
            ]
        );
        $testProductAttributes = new ProductAttributeList($testProductAttributes);

        $imageFileAttribute = ProductAttribute::fromArray(
            [
                ProductAttribute::CODE => ProductImage::FILE,
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'test.png'
            ]
        );
        $imageLabelAttribute = ProductAttribute::fromArray(
            [
                ProductAttribute::CODE => ProductImage::LABEL,
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'Product label'
            ]
        );
        $image = new ProductImage(new ProductAttributeList($imageFileAttribute, $imageLabelAttribute));
        $imageList = new ProductImageList($image);

        $stubContext = $this->getMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([VersionedContext::CODE => '123']);

        return new SimpleProduct($productId, $testProductAttributes, $imageList, $stubContext);
    }

    /**
     * @param string $idString
     * @param Product ...$childProducts
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
