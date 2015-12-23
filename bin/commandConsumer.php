#!/usr/bin/env php
<?php


namespace LizardsAndPumpkins;

require __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new TwentyOneRunFactory());

$eventConsumer = $factory->createCommandConsumer();
$eventConsumer->process();
