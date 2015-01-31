<?php

require_once '../vendor/autoload.php';

chdir(__DIR__ . '/..');

use Brera\Product\CatalogImportDomainEvent;
use Brera\PoCMasterFactory;
use Brera\SampleFactory;

$factory = new PoCMasterFactory();
$factory->register(new SampleFactory());

$xml = file_get_contents('data/product.xml');

$queue = $factory->getEventQueue();
$queue->add(new CatalogImportDomainEvent($xml));

$consumer = $factory->createDomainEventConsumer();
$numberOfMessages = 3;
$consumer->process($numberOfMessages);
