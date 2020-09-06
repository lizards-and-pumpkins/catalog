<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\XPathParser;
use PHPUnit\Framework\TestCase;

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
class ConfigurableProductXmlToProductBuilderTest extends TestCase
{
    /**
     * @var ConfigurableProductXmlToProductBuilder
     */
    private $configurableProductXmlToProductBuilder;

    final protected function setUp(): void
    {
        $stubProductXmlToProductBuilderLocatorProxy = function () {
            return $this->createMock(ProductXmlToProductBuilderLocator::class);
        };
        $this->configurableProductXmlToProductBuilder = new ConfigurableProductXmlToProductBuilder(
            $stubProductXmlToProductBuilderLocatorProxy
        );
    }

    public function testItReturnsTheConfigurableProductTypeCode(): void
    {
        $productTypeCode = $this->configurableProductXmlToProductBuilder->getSupportedProductTypeCode();
        $this->assertInstanceOf(ProductTypeCode::class, $productTypeCode);
        $this->assertEquals(ConfigurableProduct::TYPE_CODE, $productTypeCode);
    }

    public function testItReturnsAConfigurableProductBuilderInstance(): void
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
