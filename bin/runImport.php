<?php

$baseDir = __DIR__ . '/..';
require_once $baseDir . '/vendor/autoload.php';
use Brera\CommonFactory;
use Brera\Product\CatalogImportDomainEvent;
use Brera\PoCMasterFactory;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\RootTemplateChangedDomainEvent;
use Brera\SampleFactory;

$factory = new PoCMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());

$queue = $factory->getEventQueue();

$xml = file_get_contents($baseDir . '/tests/shared-fixture/product-listing-root-snippet.xml');
$queue->add(new RootTemplateChangedDomainEvent($xml));

$xml = file_get_contents($baseDir . '/tests/shared-fixture/product.xml');
$queue->add(new CatalogImportDomainEvent($xml));

$xml = file_get_contents($baseDir . '/tests/shared-fixture/product-listing.xml');
$queue->add(new ProductListingSavedDomainEvent($xml));

$consumer = $factory->createDomainEventConsumer();
$numberOfMessages = 5;
$consumer->process($numberOfMessages);

$messages = $factory->getLogger()->getMessages();
if (count($messages)) {
    echo "Log message(s):\n";
    foreach ($messages as $message) {
        echo "\t" . $message;
        if (substr($message, -1) !== PHP_EOL) {
            echo PHP_EOL;
        }
    }
}
