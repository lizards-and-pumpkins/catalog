<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductBuilder
 * @uses \Brera\PoC\Product\Product
 * @uses \Brera\PoC\Product\ProductId
 * @uses \Brera\PoC\Product\PoCSku
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldCreateAProductFromXml()
	{
		$xml = '<product>
  <sku>118235-251</sku>
  <_type>simple</_type>
  <_category>Laufshop/Shop,Laufshop/Herren/Laufzubehör,Laufshop/Damen/Laufzubehör</_category>
  <description>Pro Touch LED Armflasher&lt;br /&gt;&#13;
&lt;br /&gt;&#13;
LED Armflasher mit elastischem Band und Flasher mit variabler Blinkfolge,&#13;
Flasher abnehmbar.&#13;</description>
  <name>LED Armflasher</name>
  <short_description>Pro Touch LED Armflasher Laufzubehör Leuchten Damen,Herren</short_description>
  <price>12.95</price>
  <tax_class_id>5</tax_class_id>
  <status>1</status>
  <weight>1.000</weight>
  <qty>5.00</qty>
  <is_in_stock>1</is_in_stock>
  <backorders>0</backorders>
  <image/>
  <image_label/>
  <small_image/>
  <small_image_label/>
  <thumbnail/>
  <thumbnail_label/>
  <_super_attribute_code/>
  <_super_attribute_option/>
  <_super_attribute_price_corr/>
  <brand>Pro Touch</brand>
  <series>LED Armflasher</series>
  <gender>Damen</gender>
  <product_group>Laufzubehör</product_group>
  <style>Leuchten</style>
  <size/>
  <base_price_amount/>
  <base_price_base_amount/>
  <base_price_base_unit/>
  <base_price_unit/>
  <price_retail>12.95</price_retail>
  <product_360/>
  <size_eu/>
  <yt_link/>
</product>';

		$builder = new ProductBuilder();
		$product = $builder->createProductFromXml($xml);

		$this->assertInstanceOf(Product::class, $product);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Product\InvalidImportDataException
	 */
	public function itShouldThrowInvalidImportDataException()
	{
		$xml = 'not a valid XML string';
		(new ProductBuilder())->createProductFromXml($xml);
	}
}
