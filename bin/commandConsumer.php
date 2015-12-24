#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new TwentyOneRunFactory());

$eventConsumer = $factory->createCommandConsumer();
$eventConsumer->process();
