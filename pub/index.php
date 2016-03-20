<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;

require_once '../vendor/autoload.php';

$request = HttpRequest::fromGlobalState(file_get_contents('php://input'));
$implementationSpecificFactory = new TwentyOneRunFactory();

$website = new DefaultWebFront($request, $implementationSpecificFactory);
$website->run();
