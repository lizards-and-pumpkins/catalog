<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductAvailability;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class AssociatedProductListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $numberOfAssociatedProducts
     * @return Product[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    private function createArrayOfStubProductsWithSize($numberOfAssociatedProducts)
    {
        return array_map(function ($num) {
            $stubProduct = $this->createMock(Product::class);
            $stubProduct->method('getId')->willReturn($num);
            return $stubProduct;
        }, range(1, $numberOfAssociatedProducts));
    }

    /**
     * @param string $code
     * @param string $value
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttribute($code, $value)
    {
        $stubAttribute = $this->createMock(ProductAttribute::class);
        $stubAttribute->method('getCode')->willReturn($code);
        $stubAttribute->method('getValue')->willReturn($value);
        return $stubAttribute;
    }

    /**
     * @param string $productId
     * @param ProductAttribute[] $attributes
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProduct($productId, ProductAttribute ...$attributes)
    {
        $stubProduct = $this->createMock(Product::class);
        $getAttributesValueMap = $this->createStubProductAttributeReturnValueMap(...$attributes);
        $hasAttributesValueMap = $this->createHasProductAttributeValueMap(...$attributes);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($getAttributesValueMap);
        $stubProduct->method('hasAttribute')->willReturnMap($hasAttributesValueMap);
        $stubProduct->method('getId')->willReturn(ProductId::fromString($productId));
        return $stubProduct;
    }

    /**
     * @param ProductAttribute[] $attributes
     * @return array[]
     */
    private function createStubProductAttributeReturnValueMap(ProductAttribute ...$attributes)
    {
        return array_map(function (ProductAttribute $attribute) {
            return [$attribute->getCode(), [$attribute->getValue()]];
        }, $attributes);
    }

    /**
     * @param ProductAttribute[] $attributes
     * @return array[]
     */
    private function createHasProductAttributeValueMap(ProductAttribute ...$attributes)
    {
        return array_map(function (ProductAttribute $attribute) {
            return [$attribute->getCode(), true];
        }, $attributes);
    }

    public function testItReturnsTheInjectedProducts()
    {
        $associatedProducts = $this->createArrayOfStubProductsWithSize(2);
        $this->assertSame($associatedProducts, (new AssociatedProductList(...$associatedProducts))->getProducts());
    }

    public function testItCanBeUsedToIterateOverTheAssociatedProducts()
    {
        $associatedProducts = $this->createArrayOfStubProductsWithSize(2);
        $this->assertSame($associatedProducts, iterator_to_array(new AssociatedProductList(...$associatedProducts)));
    }

    /**
     * @param int $numberOfAssociatedProducts
     * @dataProvider numberOfAssociatedProductsProvider
     */
    public function testItIsCountable($numberOfAssociatedProducts)
    {
        $stubProducts = $this->createArrayOfStubProductsWithSize($numberOfAssociatedProducts);
        $this->assertCount($numberOfAssociatedProducts, new AssociatedProductList(...$stubProducts));
    }

    /**
     * @return array[]
     */
    public function numberOfAssociatedProductsProvider()
    {
        return [[1], [2]];
    }

    public function testItImplementsTheJsonSerializableInterface()
    {
        $stubProduct = $this->createMock(Product::class);
        $this->assertInstanceOf(\JsonSerializable::class, new AssociatedProductList($stubProduct));
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubProductAvailability */
        $stubProductAvailability = $this->createMock(ProductAvailability::class);

        $associatedProduct = new SimpleProduct(
            ProductId::fromString('test'),
            ProductTaxClass::fromString('test'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContextBuilder::rehydrateContext([DataVersion::CONTEXT_CODE => '25732342']),
            $stubProductAvailability
        );
        $sourceAssociatedProductList = new AssociatedProductList($associatedProduct);

        $json = json_encode($sourceAssociatedProductList);
        $rehydratedAssociatedProductList = AssociatedProductList::fromArray(
            json_decode($json, true),
            $stubProductAvailability
        );
        
        $this->assertInstanceOf(AssociatedProductList::class, $rehydratedAssociatedProductList);
    }

    public function testItThrowsAnExceptionIfTwoProductsWithTheSameIdAreInjectedAsAssociatedProducts()
    {
        $this->expectException(DuplicateAssociatedProductException::class);
        $this->expectExceptionMessage('The product "test" is associated two times to the same composite product');
        $stubProductOne = $this->createMock(Product::class);
        $stubProductOne->method('getId')->willReturn(ProductId::fromString('test'));
        $stubProductTwo = $this->createMock(Product::class);
        $stubProductTwo->method('getId')->willReturn(ProductId::fromString('test'));

        new AssociatedProductList($stubProductOne, $stubProductTwo);
    }

    public function testItThrowsAnExceptionIfTheValueCombinationsForTheGivenAttributesAreNotUnique()
    {
        $this->expectException(ProductAttributeValueCombinationNotUniqueException::class);
        $this->expectExceptionMessage(
            'The associated products "test1" and "test2" have the same value combination ' .
            'for the attributes "attribute_1" and "attribute_2"'
        );

        $fooAttribute1 = $this->createStubAttribute('attribute_1', 'foo1');
        $fooAttribute2 = $this->createStubAttribute('attribute_1', 'foo1');
        $barAttribute1 = $this->createStubAttribute('attribute_2', 'bar1');
        $barAttribute2 = $this->createStubAttribute('attribute_2', 'bar1');

        $stubProductOne = $this->createStubProduct('test1', $fooAttribute1, $barAttribute1);
        $stubProductTwo = $this->createStubProduct('test2', $fooAttribute2, $barAttribute2);

        $associatedProductList = new AssociatedProductList($stubProductOne, $stubProductTwo);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute('attribute_1', 'attribute_2');
    }

    public function testItThrowsNoExceptionIfTheValueCombinationsForTheGivenAttributesAreUnique()
    {
        $fooAttribute1 = $this->createStubAttribute('attribute_1', 'foo1');
        $fooAttribute2 = $this->createStubAttribute('attribute_1', 'foo2');
        $barAttribute1 = $this->createStubAttribute('attribute_2', 'bar1');
        $barAttribute2 = $this->createStubAttribute('attribute_2', 'bar2');

        $stubProductOne = $this->createStubProduct('test1', $fooAttribute1, $barAttribute1);
        $stubProductTwo = $this->createStubProduct('test2', $fooAttribute2, $barAttribute2);

        $associatedProductList = new AssociatedProductList($stubProductOne, $stubProductTwo);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute('attribute_1', 'attribute_2');
        $this->assertTrue(true, 'No exception was thrown');
    }

    public function testItThrowsAnExceptionIfAssociatedProductsAreMissingGivenAttributes()
    {
        $this->expectException(AssociatedProductIsMissingRequiredAttributesException::class);
        $this->expectExceptionMessage('The associated product "test" is missing the required attribute "attribute_2"');

        $stubAttribute = $this->createStubAttribute('attribute_1', 'foo');

        $stubProduct = $this->createStubProduct('test', $stubAttribute);

        $associatedProductList = new AssociatedProductList($stubProduct);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute('attribute_1', 'attribute_2');
    }
}
