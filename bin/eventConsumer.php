#!/usr/bin/env php
<?php

namespace Brera;

use Brera\Projection\LoggingDomainEventHandlerFactory;

require __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());
$factory->register(new LoggingDomainEventHandlerFactory());

$eventConsumer = $factory->createDomainEventConsumer();
$eventConsumer->process();
