#!/usr/bin/env php
<?php

namespace Brera;

use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;

require __DIR__ . '/../vendor/autoload.php';

class LoggingEventHandlerFactory implements Factory, DomainEventFactory
{
    use FactoryTrait;

    /**
     * @var CommonFactory
     */
    private $commonFactory;

    private function getCommonFactory()
    {
        if (null === $this->commonFactory) {
            $this->commonFactory = new CommonFactory();
            $this->commonFactory->setMasterFactory($this->getMasterFactory());
        }
        return $this->commonFactory;
    }
    
    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createProductWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param TemplateWasUpdatedDomainEvent $event
     * @return TemplateWasUpdatedDomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(TemplateWasUpdatedDomainEvent $event)
    {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ImageWasUpdatedDomainEvent $event
     * @return ImageWasUpdatedDomainEventHandler
     */
    public function createImageWasUpdatedDomainEventHandler(ImageWasUpdatedDomainEvent $event)
    {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createImageWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ProductListingWasUpdatedDomainEvent $event
     * @return ProductListingWasUpdatedDomainEventHandler
     */
    public function createProductListingWasUpdatedDomainEventHandler(ProductListingWasUpdatedDomainEvent $event)
    {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createProductListingWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ProductStockQuantityWasUpdatedDomainEvent $event
     * @return ProductStockQuantityWasUpdatedDomainEventHandler
     */
    public function createProductStockQuantityWasUpdatedDomainEventHandler(
        ProductStockQuantityWasUpdatedDomainEvent $event
    ) {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createProductStockQuantityWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event)
    {
        $commonFactory = $this->getCommonFactory();
        return $commonFactory->createProcessTimeLoggingDomainEventDecorator(
            $commonFactory->createContentBlockWasUpdatedDomainEventHandler($event)
        );
    }
}

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());
$factory->register(new LoggingEventHandlerFactory());

$eventConsumer = $factory->createDomainEventConsumer();
$eventConsumer->process();
