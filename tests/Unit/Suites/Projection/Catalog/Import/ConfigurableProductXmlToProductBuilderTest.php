<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder;
use LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductTypeCode;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToAssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductTypeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\XPathParser
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
