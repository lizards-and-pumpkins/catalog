<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductTypeCodeMissingInAssociatedProductListSourceArrayException;
use LizardsAndPumpkins\Product\Composite\Exception\UnknownProductTypeCodeInSourceArrayException;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImageList;
use LizardsAndPumpkins\Product\SimpleProduct;

/**
 * @covers \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\RehydratableProductTrait
 * @uses   \LizardsAndPumpkins\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 */
class AssociatedProductListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $numberOfAssociatedProducts
     * @return Product[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    private function createArrayOfStubProductsWithSize($numberOfAssociatedProducts)
    {
        return array_map(
            function ($num) {
                $stubProduct = $this->getMock(Product::class);
                $stubProduct->method('getId')->willReturn($num);
                return $stubProduct;
            },
            range(1, $numberOfAssociatedProducts)
        );
    }

    /**
     * @param string $code
     * @param string $value
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttribute($code, $value)
    {
        $stubAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubAttribute->method('getCode')->willReturn($code);
        $stubAttribute->method('getValue')->willReturn($value);
        return $stubAttribute;
    }

    /**
     * @param string $productId
     * @param ProductAttribute ...$attributes
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProduct($productId, ProductAttribute ...$attributes)
    {
        $stubProduct = $this->getMock(Product::class);
        $getAttributesValueMap = $this->createStubProductAttributeReturnValueMap(...$attributes);
        $hasAttributesValueMap = $this->createHasProductAttributeValueMap(...$attributes);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($getAttributesValueMap);
        $stubProduct->method('hasAttribute')->willReturnMap($hasAttributesValueMap);
        $stubProduct->method('getId')->willReturn(ProductId::fromString($productId));
        return $stubProduct;
    }

    /**
     * @param ProductAttribute $attributes
     * @return array[]
     */
    private function createStubProductAttributeReturnValueMap(ProductAttribute ...$attributes)
    {
        return array_map(
            function (ProductAttribute $attribute) {
                return [$attribute->getCode(), [$attribute->getValue()]];
            },
            $attributes
        );
    }

    /**
     * @param ProductAttribute ...$attributes
     * @return array[]
     */
    private function createHasProductAttributeValueMap(ProductAttribute ...$attributes)
    {
        return array_map(
            function (ProductAttribute $attribute) {
                return [$attribute->getCode(), true];
            },
            $attributes
        );
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
        $stubProduct = $this->getMock(Product::class);
        $this->assertInstanceOf(\JsonSerializable::class, new AssociatedProductList($stubProduct));
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $associatedProduct = new SimpleProduct(
            ProductId::fromString('test'),
            new ProductAttributeList(),
            new ProductImageList(),
            ContextBuilder::rehydrateContext([VersionedContext::CODE => '25732342'])
        );
        $sourceAssociatedProductList = new AssociatedProductList($associatedProduct);

        $json = json_encode($sourceAssociatedProductList);
        $rehydratedAssociatedProductList = AssociatedProductList::fromArray(json_decode($json, true));
        $this->assertInstanceOf(AssociatedProductList::class, $rehydratedAssociatedProductList);
    }

    public function testItThrowsAnExceptionIfAProductTypeCodeIsMissingFromSourceArray()
    {
        $this->setExpectedException(
            ProductTypeCodeMissingInAssociatedProductListSourceArrayException::class,
            'The product type code index is missing from an associated product source array'
        );
        AssociatedProductList::fromArray(
            [
                [
                    'product_id' => 'the-type-code-key-is-missing-from-this-array (amongst-other-keys)'
                ]
            ]
        );
    }

    public function testItThrowsAnExceptionIfAAnUnknownProductTypeCodeIsSetInTheProductSourceArray()
    {
        $this->setExpectedException(
            UnknownProductTypeCodeInSourceArrayException::class,
            'The product type code "dimple" is unknown'
        );
        AssociatedProductList::fromArray([[Product::TYPE_KEY => 'dimple']]);
    }

    public function testItThrowsAnExceptionIfTwoProductsWithTheSameIdAreInjectedAsAssociatedProducts()
    {
        $this->setExpectedException(
            DuplicateAssociatedProductException::class,
            'The product "test" is associated two times to the same composite product'
        );
        $stubProductOne = $this->getMock(Product::class);
        $stubProductOne->method('getId')->willReturn(ProductId::fromString('test'));
        $stubProductTwo = $this->getMock(Product::class);
        $stubProductTwo->method('getId')->willReturn(ProductId::fromString('test'));

        new AssociatedProductList($stubProductOne, $stubProductTwo);
    }

    public function testItThrowsAnExceptionIfTheValueCombinationsForTheGivenAttributesAreNotUnique()
    {
        $this->setExpectedException(
            ProductAttributeValueCombinationNotUniqueException::class,
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
        $this->setExpectedException(
            AssociatedProductIsMissingRequiredAttributesException::class,
            'The associated product "test" is missing the required attribute "attribute_2"'
        );

        $stubAttribute = $this->createStubAttribute('attribute_1', 'foo');

        $stubProduct = $this->createStubProduct('test', $stubAttribute);

        $associatedProductList = new AssociatedProductList($stubProduct);

        $associatedProductList->validateUniqueValueCombinationForEachProductAttribute('attribute_1', 'attribute_2');
    }
}
