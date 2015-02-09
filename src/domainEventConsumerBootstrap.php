<?php

namespace Brera;

// Use a "real" factory and then this is the consumer bootstrap code
$factory = new CommonFactory();
$consumer = $factory->createDomainEventConsumer();
$consumer->process(1);
