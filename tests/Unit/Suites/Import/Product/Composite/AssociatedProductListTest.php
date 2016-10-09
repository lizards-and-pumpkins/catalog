<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
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
    private function createArrayOfStubProductsWithSize(int $numberOfAssociatedProducts) : array
    {
        return array_map(function ($num) {
            $stubProduct = $this->createMock(Product::class);
            $stubProduct->method('getId')->willReturn($num);
            return $stubProduct;
        }, range(1, $numberOfAssociatedProducts));
    }

    private function createStubAttribute(AttributeCode $attributeCode, string $value) : ProductAttribute
    {
        $stubAttribute = $this->createMock(ProductAttribute::class);
        $stubAttribute->method('getCode')->willReturn($attributeCode);
        $stubAttribute->method('getValue')->willReturn($value);
        return $stubAttribute;
    }

    private function createStubProduct(string $productId, ProductAttribute ...$attributes) : Product
    {
        $stubProduct = $this->createMock(Product::class);
        $getAttributesValueMap = $this->createStubProductAttributeReturnValueCallback(...$attributes);
        $hasAttributesValueCallback = $this->createHasProductAttributeValueCallback(...$attributes);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnCallback($getAttributesValueMap);
        $stubProduct->method('hasAttribute')->willReturnCallback($hasAttributesValueCallback);
        $stubProduct->method('getId')->willReturn(new ProductId($productId));
        return $stubProduct;
    }

    private function createStubProductAttributeReturnValueCallback(ProductAttribute ...$attributes) : callable
    {
        return function (string $attributeCode) use ($attributes) {
            return array_reduce($attributes, function(array $carry, ProductAttribute $attribute) use ($attributeCode) {
                if ((string) $attribute->getCode() !== $attributeCode) {
                    return $carry;
                }
                return array_merge($carry, [$attribute->getValue()]);
            }, []);
        };
    }

    private function createHasProductAttributeValueCallback(ProductAttribute ...$attributes) : callable
    {
        return function (AttributeCode $attributeCode) use ($attributes) {
            foreach ($attributes as $attribute) {
                if ($attributeCode->isEqualTo($attribute->getCode())) {
                    return true;
                }
            }

            return false;
        };
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
     * @dataProvider numberOfAssociatedProductsProvider
     */
    public function testItIsCountable(int $numberOfAssociatedProducts)
    {
        $stubProducts = $this->createArrayOfStubProductsWithSize($numberOfAssociatedProducts);
        $this->assertCount($numberOfAssociatedProducts, new AssociatedProductList(...$stubProducts));
    }

    /**
     * @return array[]
     */
    public function numberOfAssociatedProductsProvider() : array
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
        $associatedProduct = new SimpleProduct(
            new ProductId('test'),
            ProductTaxClass::fromString('test'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContextBuilder::rehydrateContext([DataVersion::CONTEXT_CODE => '25732342'])
        );
        $sourceAssociatedProductList = new AssociatedProductList($associatedProduct);

        $json = json_encode($sourceAssociatedProductList);
        $rehydratedAssociatedProductList = AssociatedProductList::fromArray(json_decode($json, true));
        $this->assertInstanceOf(AssociatedProductList::class, $rehydratedAssociatedProductList);
    }

    public function testItThrowsAnExceptionIfTwoProductsWithTheSameIdAreInjectedAsAssociatedProducts()
    {
        $this->expectException(DuplicateAssociatedProductException::class);
        $this->expectExceptionMessage('The product "test" is associated two times to the same composite product');
        $stubProductOne = $this->createMock(Product::class);
        $stubProductOne->method('getId')->willReturn(new ProductId('test'));
        $stubProductTwo = $this->createMock(Product::class);
        $stubProductTwo->method('getId')->willReturn(new ProductId('test'));

        new AssociatedProductList($stubProductOne, $stubProductTwo);
    }

    public function testItThrowsAnExceptionIfTheValueCombinationsForTheGivenAttributesAreNotUnique()
    {
        $this->expectException(ProductAttributeValueCombinationNotUniqueException::class);
        $this->expectExceptionMessage(
            'The associated products "test1" and "test2" have the same value combination ' .
            'for the attributes "code_a" and "code_b"'
        );

        $dummyAttributeCodeA = AttributeCode::fromString('code_a');
        $dummyAttributeCodeB = AttributeCode::fromString('code_b');

        $fooAttribute1 = $this->createStubAttribute($dummyAttributeCodeA, 'foo1');
        $fooAttribute2 = $this->createStubAttribute($dummyAttributeCodeA, 'foo1');
        $barAttribute1 = $this->createStubAttribute($dummyAttributeCodeB, 'bar1');
        $barAttribute2 = $this->createStubAttribute($dummyAttributeCodeB, 'bar1');

        $stubProductOne = $this->createStubProduct('test1', $fooAttribute1, $barAttribute1);
        $stubProductTwo = $this->createStubProduct('test2', $fooAttribute2, $barAttribute2);

        $associatedProductList = new AssociatedProductList($stubProductOne, $stubProductTwo);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute(
            $dummyAttributeCodeA,
            $dummyAttributeCodeB
        );
    }

    public function testItThrowsNoExceptionIfTheValueCombinationsForTheGivenAttributesAreUnique()
    {
        $dummyAttributeCodeA = AttributeCode::fromString('code_a');
        $dummyAttributeCodeB = AttributeCode::fromString('code_b');

        $fooAttribute1 = $this->createStubAttribute($dummyAttributeCodeA, 'foo1');
        $fooAttribute2 = $this->createStubAttribute($dummyAttributeCodeA, 'foo2');
        $barAttribute1 = $this->createStubAttribute($dummyAttributeCodeB, 'bar1');
        $barAttribute2 = $this->createStubAttribute($dummyAttributeCodeB, 'bar2');

        $stubProductOne = $this->createStubProduct('test1', $fooAttribute1, $barAttribute1);
        $stubProductTwo = $this->createStubProduct('test2', $fooAttribute2, $barAttribute2);

        $associatedProductList = new AssociatedProductList($stubProductOne, $stubProductTwo);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute(
            $dummyAttributeCodeA,
            $dummyAttributeCodeB
        );
        $this->assertTrue(true, 'No exception was thrown');
    }

    public function testItThrowsAnExceptionIfAssociatedProductsAreMissingGivenAttributes()
    {
        $this->expectException(AssociatedProductIsMissingRequiredAttributesException::class);
        $this->expectExceptionMessage('The associated product "test" is missing the required attribute "code_b"');

        $dummyAttributeCodeA = AttributeCode::fromString('code_a');
        $dummyAttributeCodeB = AttributeCode::fromString('code_b');
        $stubAttribute = $this->createStubAttribute($dummyAttributeCodeA, 'foo');

        $stubProduct = $this->createStubProduct('test', $stubAttribute);

        $associatedProductList = new AssociatedProductList($stubProduct);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute(
            $dummyAttributeCodeA,
            $dummyAttributeCodeB
        );
    }
}
