#!/usr/bin/env php
<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;

require __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new TwentyOneRunFactory());
$factory->register(new LoggingDomainEventHandlerFactory());

$eventConsumer = $factory->createDomainEventConsumer();
$eventConsumer->process();
