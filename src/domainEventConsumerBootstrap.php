<?php

namespace Brera\PoC;

require __DIR__ . '/autoload.php';

// Use a "real" factory and then this is the consumer bootstrap code
$factory = new IntegrationTestFactory();
$consumer = $factory->createDomainEventConsumer();
$consumer->process(1);
