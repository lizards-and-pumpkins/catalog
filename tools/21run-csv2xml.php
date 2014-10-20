#!/usr/bin/env php
<?php

$file = @$argv[1] ?: 'php://stdin';
$f = fopen($file, 'r');

$ignoreFields = [
    'update_delete',
    '_store',
    '_attribute_set',
    '_product_websites',
    'manage_stock',
    'use_config_manage_stock',
    'use_config_backorders',
    'visibility',
    '_media_image',
    '_media_attribute_id',
    '_media_is_disabled',
    '_media_position',
    '_media_lable',
    '_links_related_sku',
    '_links_related_position',
    '_links_crosssell_sku',
    '_links_crosssell_position',
    '_links_upsell_sku',
    '_links_upsell_position',
    '_associated_sku',
    '_associated_default_qty',
    '_associated_position',
    '_tier_price_website',
    '_tier_price_customer_group',
    '_tier_price_qty',
    '_tier_price_price',
    '_super_products_sku',
    'google_base_title',
    'google_product_type'
];

$fields = [];
$dom = new DOMDocument();
$dom->formatOutput = true;
echo '<?xml version="1.0"?>' . PHP_EOL;
echo "<products>\n";

while (! feof($f)) {
    $row = fgetcsv($f);
    if (empty($fields)) {
        $fields = $row;
        continue;
    }
    $product = $dom->createElement('product');
    foreach ($fields as $i => $field) {
        if (in_array($field, $ignoreFields)) {
            continue;
        }
        $attribute = $dom->createElement($field, htmlentities($row[$i]));
        $product->appendChild($attribute);
    }
    echo $dom->saveXML($product) . PHP_EOL;
}
echo "</products>\n";
 
