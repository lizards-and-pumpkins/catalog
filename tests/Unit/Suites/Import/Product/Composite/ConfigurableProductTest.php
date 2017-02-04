<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Exception\ProductTypeCodeMismatchException;
use LizardsAndPumpkins\Import\Product\Exception\ProductTypeCodeMissingException;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ConfigurableProductAssociatedProductListInvariantViolationException;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct
 * @covers \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class ConfigurableProductTest extends TestCase
{
    /**
     * @var ConfigurableProduct
     */
    private $configurableProduct;

    /**
     * @var SimpleProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSimpleProduct;

    /**
     * @var ProductVariationAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockVariationAttributeList;

    /**
     * @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAssociatedProductList;

    private function createConfigurableProductInstance() : ConfigurableProduct
    {
        return new ConfigurableProduct(
            $this->mockSimpleProduct,
            $this->mockVariationAttributeList,
            $this->mockAssociatedProductList
        );
    }

    protected function setUp()
    {
        $this->mockSimpleProduct = $this->createMock(SimpleProduct::class);
        $this->mockVariationAttributeList = $this->createMock(ProductVariationAttributeList::class);
        $this->mockVariationAttributeList->method('getAttributes')->willReturn([]);
        $this->mockAssociatedProductList = $this->createMock(AssociatedProductList::class);
        $this->configurableProduct = $this->createConfigurableProductInstance();
    }

    public function testItImplementsTheProductInterface()
    {
        $this->assertInstanceOf(Product::class, $this->configurableProduct);
    }

    public function testCompositeProductInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CompositeProduct::class, $this->configurableProduct);
    }

    public function testItDelegatesToTheSimpleProductToFetchTheId()
    {
        $testId = new ProductId('foo');
        $this->mockSimpleProduct->method('getId')->willReturn($testId);
        $this->assertSame($testId, $this->configurableProduct->getId());
    }

    public function testItDelegatesToTheSimpleProductToGetAFirstAttributeValueByCode()
    {
        $this->mockSimpleProduct->method('getFirstValueOfAttribute')->willReturnMap(
            [
                ['attribute_a', 'value A'],
                ['attribute_b', 'value B'],
            ]
        );
        $this->assertSame('value A', $this->configurableProduct->getFirstValueOfAttribute('attribute_a'));
        $this->assertSame('value B', $this->configurableProduct->getFirstValueOfAttribute('attribute_b'));
    }

    public function testItDelegatesToTheSimpleProductToGetAttributeValuesByCode()
    {
        $attributeCode = 'attribute_code';
        $testValues = ['value A', 'value B', 'value C'];
        $this->mockSimpleProduct->method('getAllValuesOfAttribute')->with($attributeCode)->willReturn($testValues);
        $this->assertSame($testValues, $this->configurableProduct->getAllValuesOfAttribute($attributeCode));
    }

    public function testItDelegatesToTheSimpleProductToCheckIfAnAttributeIsPresent()
    {
        $dummyAttributeCode = AttributeCode::fromString('test');
        $this->mockSimpleProduct->method('hasAttribute')->with($dummyAttributeCode)->willReturn(true);
        $this->assertTrue($this->configurableProduct->hasAttribute($dummyAttributeCode));
    }

    public function testItDelegatesToTheSimpleProductToGetAllAttributes()
    {
        $dummyAttributeList = $this->createMock(ProductAttributeList::class);
        $this->mockSimpleProduct->method('getAttributes')->willReturn($dummyAttributeList);
        $this->assertSame($dummyAttributeList, $this->configurableProduct->getAttributes());
    }

    public function testItIncludesTheCompositeObjectsInTheJsonRepresentation()
    {
        $this->mockSimpleProduct->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->mockAssociatedProductList->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->mockVariationAttributeList->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $result = $this->configurableProduct->jsonSerialize();
        $this->assertArrayHasKey(Product::TYPE_KEY, $result);
        $this->assertSame(ConfigurableProduct::TYPE_CODE, $result[Product::TYPE_KEY]);
    }

    public function testItCanBeCreatedFromAnArray()
    {
        $result = ConfigurableProduct::fromArray([
            Product::TYPE_KEY => ConfigurableProduct::TYPE_CODE,
            ConfigurableProduct::SIMPLE_PRODUCT => [
                Product::TYPE_KEY => SimpleProduct::TYPE_CODE,
                'product_id' => 'test',
                'tax_class' => 'test tax class',
                'attributes' => [],
                'images' => [],
                'context' => [DataVersion::CONTEXT_CODE => '123']
            ],
            ConfigurableProduct::VARIATION_ATTRIBUTES => ['foo'],
            ConfigurableProduct::ASSOCIATED_PRODUCTS => [
                'product_php_classes' => [],
                'products' => []
            ]
        ]);
        $this->assertInstanceOf(ConfigurableProduct::class, $result);
    }

    public function testItThrowsAnExceptionIfTheTypeCodeIsMissingFromSourceArray()
    {
        $allFieldsExceptTypeCode = [
            ConfigurableProduct::SIMPLE_PRODUCT => [],
            ConfigurableProduct::VARIATION_ATTRIBUTES => [],
            ConfigurableProduct::ASSOCIATED_PRODUCTS => []
        ];
        $this->expectException(ProductTypeCodeMissingException::class);
        $this->expectExceptionMessage(sprintf('The array key "%s" is missing from source array', Product::TYPE_KEY));
        ConfigurableProduct::fromArray($allFieldsExceptTypeCode);
    }

    /**
     * @param mixed $invalidTypeCode
     * @param string $typeCodeString
     * @dataProvider invalidProductTypeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheTypeCodeInSourceArrayDoesNotMatch(
        $invalidTypeCode,
        string $typeCodeString
    ) {
        $this->expectException(ProductTypeCodeMismatchException::class);
        $this->expectExceptionMessage(
            sprintf('Expected the product type code string "configurable", got "%s"', $typeCodeString)
        );
        ConfigurableProduct::fromArray([
            Product::TYPE_KEY => $invalidTypeCode,
            ConfigurableProduct::SIMPLE_PRODUCT => [],
            ConfigurableProduct::VARIATION_ATTRIBUTES => [],
            ConfigurableProduct::ASSOCIATED_PRODUCTS => []
        ]);
    }

    /**
     * @return array[]
     */
    public function invalidProductTypeCodeProvider() : array
    {
        return [
            ['c0nf1gur4b13', 'c0nf1gur4b13'],
            [$this, get_class($this)],
            [123, 'integer'],
        ];
    }

    public function testItReturnsTheContextFromTheSimpleProductComponent()
    {
        $stubContext = $this->createMock(Context::class);
        $this->mockSimpleProduct->method('getContext')->willReturn($stubContext);
        $this->assertSame($stubContext, $this->configurableProduct->getContext());
    }

    public function testItDelegatesToTheSimpleProductToFetchTheImagesList()
    {
        $stubImageList = $this->createMock(ProductImageList::class);
        $this->mockSimpleProduct->method('getImages')->willReturn($stubImageList);
        $this->assertSame($stubImageList, $this->configurableProduct->getImages());
    }

    public function testItDelegatesToTheSimpleProductToGetTheImageCount()
    {
        $this->mockSimpleProduct->method('getImageCount')->willReturn(42);
        $this->assertSame(42, $this->configurableProduct->getImageCount());
    }

    public function testItDelegatesToTheSimpleProductToGetAnImageByNumber()
    {
        $stubImage = $this->createMock(ProductImage::class);
        $this->mockSimpleProduct->method('getImageByNumber')->with(0)->willReturn($stubImage);
        $this->assertSame($stubImage, $this->configurableProduct->getImageByNumber(0));
    }

    public function testItDelegatesToTheSimpleProductToGetAnImageFileNameByNumber()
    {
        $testFileName = 'test.jpg';
        $this->mockSimpleProduct->method('getImageFileNameByNumber')->with(0)->willReturn($testFileName);
        $this->assertSame($testFileName, $this->configurableProduct->getImageFileNameByNumber(0));
    }

    public function testItDelegatesToTheSimpleProductToGetAnImageLabelByNumber()
    {
        $testLabel = 'Test Label';
        $this->mockSimpleProduct->method('getImageLabelByNumber')->with(0)->willReturn($testLabel);
        $this->assertSame($testLabel, $this->configurableProduct->getImageLabelByNumber(0));
    }

    public function testItDelegatesToTheSimpleProductToGetTheMainProductImageFileName()
    {
        $testFileName = 'test.jpg';
        $this->mockSimpleProduct->method('getMainImageFileName')->willReturn($testFileName);
        $this->assertSame($testFileName, $this->configurableProduct->getMainImageFileName());
    }

    public function testItDelegatesToTheSimpleProductToGetTheMainProductImageLabel()
    {
        $testLabel = 'Test Label';
        $this->mockSimpleProduct->method('getMainImageLabel')->willReturn($testLabel);
        $this->assertSame($testLabel, $this->configurableProduct->getMainImageLabel());
    }

    public function testItReturnsAProductVariationAttributeList()
    {
        $this->assertSame(
            $this->mockVariationAttributeList,
            $this->configurableProduct->getVariationAttributes()
        );
    }

    public function testItReturnsTheAssociatedProductsList()
    {
        $this->assertSame($this->mockAssociatedProductList, $this->configurableProduct->getAssociatedProducts());
    }

    /**
     * @dataProvider associatedProductListValidationFailureExceptionProvider
     */
    public function testItThrowsAnExceptionIfAnAssociatedProductIsMissingVariationAttributes(
        AssociatedProductListDomainException $exception
    ) {
        $this->expectException(ConfigurableProductAssociatedProductListInvariantViolationException::class);
        $this->expectExceptionMessage('Invalid configurable product "test":');

        $dummyProductId = new ProductId('test');
        $this->mockSimpleProduct->method('getId')->willReturn($dummyProductId);

        $this->mockAssociatedProductList->method('validateUniqueValueCombinationForEachProductAttribute')
            ->willThrowException($exception);
        $this->createConfigurableProductInstance();
    }

    /**
     * @return array[]
     */
    public function associatedProductListValidationFailureExceptionProvider() : array
    {
        return [
            [new AssociatedProductIsMissingRequiredAttributesException()],
            [new ProductAttributeValueCombinationNotUniqueException()]
        ];
    }

    public function testItDelegatesToTheSimpleProductToGetTheTaxClass()
    {
        $stubProductTaxClass = $this->createMock(ProductTaxClass::class);
        $this->mockSimpleProduct->method('getTaxClass')->willReturn($stubProductTaxClass);
        $this->assertSame($stubProductTaxClass, $this->configurableProduct->getTaxClass());
    }
}
