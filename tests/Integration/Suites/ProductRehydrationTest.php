<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use PHPUnit\Framework\TestCase;

class ProductRehydrationTest extends TestCase
{
    private function assertBasicProductPropertyEqual(Product $sourceProduct, Product $rehydratedProduct): void
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

    private function assertProductEquals(Product $sourceProduct, Product $rehydratedProduct): void
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
            $sourceVariationAttributeList->getIterator()->count(),
            $rehydratedVariationAttributeList->getIterator()->count()
        );
        
        /**
         * @var AttributeCode $attribute
         */
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

    private function createSimpleProductWithId(string $productIdString) : SimpleProduct
    {
        $productId = new ProductId($productIdString);
        
        $productTaxClass = ProductTaxClass::fromString('test');
        
        $testProductAttribute = $this->createProductAttribute('foo', uniqid());
        $testProductAttributes = new ProductAttributeList($testProductAttribute);

        $imageFileAttribute = $this->createProductAttribute(ProductImage::FILE, 'test.jpg');
        $imageLabelAttribute = $this->createProductAttribute(ProductImage::LABEL, 'Product Label');
        
        $image = new ProductImage(new ProductAttributeList($imageFileAttribute, $imageLabelAttribute));
        $imageList = new ProductImageList($image);

        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);

        return new SimpleProduct($productId, $productTaxClass, $testProductAttributes, $imageList, $stubContext);
    }

    private function createProductAttribute(string $code, string $value) : ProductAttribute
    {
        $contextData = [];
        return new ProductAttribute(AttributeCode::fromString($code), $value, $contextData);
    }

    private function createConfigurableProductWithIdAndAssociatedProducts(
        string $idString,
        Product ...$childProducts
    ) : ConfigurableProduct {
        $simpleProduct = $this->createSimpleProductWithId($idString);

        $variationAttributes = new ProductVariationAttributeList(AttributeCode::fromString('foo'));
        $associatedProducts = new AssociatedProductList(...$childProducts);
        return new ConfigurableProduct($simpleProduct, $variationAttributes, $associatedProducts);
    }

    public function testASimpleProductCanBeJsonSerializedAndRehydrated(): void
    {
        $sourceSimpleProduct = $this->createSimpleProductWithId('test');
        $json = json_encode($sourceSimpleProduct);
        $rehydratedSimpleProduct = SimpleProduct::fromArray(json_decode($json, true));

        $this->assertSimpleProductEquals($sourceSimpleProduct, $rehydratedSimpleProduct);
    }

    public function testAConfigurableProductCanBeJsonSerializedAndRehydrated(): void
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
