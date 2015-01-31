<?php

namespace Brera;

use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;

require_once '../vendor/autoload.php';

$httpUrl = HttpUrl::fromString('http://example.com/whatever');
$request = HttpRequest::fromParameters('GET', $httpUrl);

$sku = PoCSku::fromString('118235-251');
$productId = ProductId::fromSku($sku);

$factory = new PoCMasterFactory();
$factory->register(new FrontendFactory());
$factory->register(new SampleFactory());

$dataPoolReader = $factory->createDataPoolReader();
$html = $dataPoolReader->getSnippet('product_detail_view_1_118235-251');

$dataPoolWriter = $factory->createDataPoolWriter();
$dataPoolWriter->setProductIdBySeoUrl($productId, $httpUrl);
$dataPoolWriter->setPoCProductHtml($productId, $html);

$website = new PoCWebFront($request, $factory);
$website->run();
