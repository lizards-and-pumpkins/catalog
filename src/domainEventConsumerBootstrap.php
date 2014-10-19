<?php

namespace Brera\PoC;

// Use a "real" factory and then this is the consumer bootstrap code
$factory = new IntegrationTestFactory();
$consumer = $factory->createDomainEventConsumer();
$consumer->process(1);
