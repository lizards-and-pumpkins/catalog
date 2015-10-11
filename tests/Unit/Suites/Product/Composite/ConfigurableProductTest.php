<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductListDomainException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMissingException;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Product\Composite\Exception\ConfigurableProductAssociatedProductListInvariantViolationException;

/**
 * @covers \LizardsAndPumpkins\Product\Composite\ConfigurableProduct
 * @covers \LizardsAndPumpkins\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 */
class ConfigurableProductTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @return ConfigurableProduct
     */
    private function createConfigurableProductInstance()
    {
        return new ConfigurableProduct(
            $this->mockSimpleProduct,
            $this->mockVariationAttributeList,
            $this->mockAssociatedProductList
        );
    }

    protected function setUp()
    {
        $this->mockSimpleProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $this->mockVariationAttributeList = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $this->mockVariationAttributeList->method('getAttributes')->willReturn(['attribute_1', 'attribute_2']);
        $this->mockAssociatedProductList = $this->getMock(AssociatedProductList::class);
        $this->configurableProduct = $this->createConfigurableProductInstance();
    }

    public function testItImplementsTheProductInterface()
    {
        $this->assertInstanceOf(Product::class, $this->configurableProduct);
    }

    public function testItDelegatesToTheSimpleProductToFetchTheId()
    {
        $testId = new \stdClass();
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
        $this->mockSimpleProduct->method('hasAttribute')->with('test')->willReturn(true);
        $this->assertTrue($this->configurableProduct->hasAttribute('test'));
    }

    public function testItIncludesTheCompositeObjectsInTheJsonRepresentation()
    {
        $this->mockSimpleProduct->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->mockAssociatedProductList->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->mockVariationAttributeList->expects($this->once())->method('jsonSerialize')->willReturn([]);
        json_encode($this->configurableProduct);
        $result = $this->configurableProduct->jsonSerialize();
        $this->assertArrayHasKey(Product::TYPE_KEY, $result);
        $this->assertSame(ConfigurableProduct::TYPE_CODE, $result[Product::TYPE_KEY]);
    }

    public function testItCanBeCreatedFromAnArray()
    {
        $result = ConfigurableProduct::fromArray([
            Product::TYPE_KEY => ConfigurableProduct::TYPE_CODE,
            'simple_product' => [
                Product::TYPE_KEY => SimpleProduct::TYPE_CODE,
                'product_id' => 'test',
                'attributes' => [],
                'images' => [],
                'context' => [VersionedContext::CODE => '123']
            ],
            'variation_attributes' => ['foo'],
            'associated_products' => [
                'product_php_classes' => [],
                'products' => []
            ]
        ]);
        $this->assertInstanceOf(ConfigurableProduct::class, $result);
    }

    public function testItThrowsAnExceptionIfTheTypeCodeIsMissingFromSourceArray()
    {
        $allFieldsExceptTypeCode = [
            'simple_product' => [],
            'variation_attributes' => [],
            'associated_products' => []
        ];
        $this->setExpectedException(
            ProductTypeCodeMissingException::class,
            sprintf('The array key "%s" is missing from source array', Product::TYPE_KEY)
        );
        ConfigurableProduct::fromArray($allFieldsExceptTypeCode);
    }

    /**
     * @param mixed $invalidTypeCode
     * @param string $typeCodeString
     * @dataProvider invalidProductTypeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheTypeCodeInSourceArrayDoesNotMatch($invalidTypeCode, $typeCodeString)
    {
        $this->setExpectedException(
            ProductTypeCodeMismatchException::class,
            sprintf('Expected the product type code string "configurable", got "%s"', $typeCodeString)
        );
        ConfigurableProduct::fromArray([
            Product::TYPE_KEY => $invalidTypeCode,
            'simple_product' => [],
            'variation_attributes' => [],
            'associated_products' => []
        ]);
    }

    /**
     * @return array[]
     */
    public function invalidProductTypeCodeProvider()
    {
        return [
            ['c0nf1gur4b13', 'c0nf1gur4b13'],
            [$this, get_class($this)],
            [123, 'integer'],
        ];
    }

    public function testItReturnsTheContextFromTheSimpleProductComponent()
    {
        $testContext = new \stdClass();
        $this->mockSimpleProduct->method('getContext')->willReturn($testContext);
        $this->assertSame($testContext, $this->configurableProduct->getContext());
    }

    public function testItDelegatesToTheSimpleProductToFetchTheImagesList()
    {
        $testImageList = new \stdClass;
        $this->mockSimpleProduct->method('getImages')->willReturn($testImageList);
        $this->assertSame($testImageList, $this->configurableProduct->getImages());
    }

    public function testItDelegatesToTheSimpleProductToGetTheImageCount()
    {
        $this->mockSimpleProduct->method('getImageCount')->willReturn(42);
        $this->assertSame(42, $this->configurableProduct->getImageCount());
    }

    public function testItDelegatesToTheSimpleProductToGetAnImageByNumber()
    {
        $stubImage = new \stdClass();
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
     * @param AssociatedProductListDomainException $exception
     * @dataProvider associatedProductListValidationFailureExceptionProvider
     */
    public function testItThrowsAnExceptionIfAnAssociatedProductIsMissingVariationAttributes(
        AssociatedProductListDomainException $exception
    ) {
        $this->setExpectedException(
            ConfigurableProductAssociatedProductListInvariantViolationException::class,
            'Invalid configurable product "test":'
        );
        $this->mockSimpleProduct->method('getId')->willReturn('test');

        $this->mockAssociatedProductList->method('validateUniqueValueCombinationForEachProductAttribute')
            ->willThrowException($exception);
        $this->createConfigurableProductInstance();
    }

    /**
     * @return array[]
     */
    public function associatedProductListValidationFailureExceptionProvider()
    {
        return [
            [new AssociatedProductIsMissingRequiredAttributesException()],
            [new ProductAttributeValueCombinationNotUniqueException()]
        ];
    }
}
