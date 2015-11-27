<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Utils\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToAssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToVariationAttributeList
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductTypeCode
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class ConfigurableProductXmlToProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductXmlToProductBuilder
     */
    private $configurableProductXmlToProductBuilder;

    protected function setUp()
    {
        $stubProductXmlToProductBuilderLocatorProxy = function () {
            return $this->getMock(ProductXmlToProductBuilderLocator::class);
        };
        $this->configurableProductXmlToProductBuilder = new ConfigurableProductXmlToProductBuilder(
            $stubProductXmlToProductBuilderLocatorProxy
        );
    }

    public function testItReturnsTheConfigurableProductTypeCode()
    {
        $productTypeCode = $this->configurableProductXmlToProductBuilder->getSupportedProductTypeCode();
        $this->assertInstanceOf(ProductTypeCode::class, $productTypeCode);
        $this->assertEquals(ConfigurableProduct::TYPE_CODE, $productTypeCode);
    }

    public function testItReturnsAConfigurableProductBuilderInstance()
    {
        $xml = '
<product type="configurable" sku="test" tax_class="test">
    <variations>
        <attribute>test</attribute>
    </variations>
</product>';
        $testXPathParser = new XPathParser($xml);
        $builder = $this->configurableProductXmlToProductBuilder->createProductBuilder($testXPathParser);
        $this->assertInstanceOf(ConfigurableProductBuilder::class, $builder);
    }
}
