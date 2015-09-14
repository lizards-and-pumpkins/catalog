#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());

$factory->createDataPoolWriter()->clear();
$factory->createCommandQueue()->clear();
$factory->createEventQueue()->clear();

printf("Cleared data pool and queues\n");
printf("Storage dir: %s\n", sys_get_temp_dir() . '/lizards-and-pumpkins');
