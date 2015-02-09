<?php

namespace Brera;

use Brera\Environment\VersionedEnvironment;
use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;

require_once '../vendor/autoload.php';

$url = HttpUrl::fromString('http://example.com/led-arm-signallampe');
$request = HttpRequest::fromParameters('GET', $url);

$environment = new VersionedEnvironment([VersionedEnvironment::CODE => DataVersion::fromVersionString('1')]);

$factory = new PoCMasterFactory();
$factory->register(new FrontendFactory());
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());

$dataPoolReader = $factory->createDataPoolReader();

$pageKeyGenerator = new PageKeyGenerator($environment);
$pageBuilder = new PageBuilder($pageKeyGenerator, $dataPoolReader);
$page = $pageBuilder->buildPage($url);

echo $page->getBody();

//$website = new PoCWebFront($request, $factory);
//$website->run();
