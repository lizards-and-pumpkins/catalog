<?php

namespace Brera;

use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;

require_once '../vendor/autoload.php';

$url = HttpUrl::fromString('http://example.com/led-arm-signallampe');
$request = HttpRequest::fromParameters('GET', $url);

$website = new PoCWebFront($request);
$website->registerFactory(new SampleFactory());
$website->run();
