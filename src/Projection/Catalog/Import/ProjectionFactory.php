<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;

class ProjectionFactory implements Factory
{
    use FactoryTrait;
    
    /**
     * @return CatalogImport
     */
    public function createCatalogImport()
    {
        return new CatalogImport(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createProductXmlToProductBuilderLocator(),
            $this->getMasterFactory()->createProductListingCriteriaBuilder(),
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @param CatalogWasImportedDomainEvent $event
     * @return CatalogWasImportedDomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(CatalogWasImportedDomainEvent $event)
    {
        $projector = $this->createProductListingPageSnippetProjector();
        return new CatalogWasImportedDomainEventHandler($event, $projector);
    }

    /**
     * @return ProductXmlToProductBuilderLocator
     */
    public function createProductXmlToProductBuilderLocator()
    {
        $productXmlToProductTypeBuilders = $this->getMasterFactory()->createProductXmlToProductTypeBuilders();
        return new ProductXmlToProductBuilderLocator(...$productXmlToProductTypeBuilders);
    }

    /**
     * @return ProductXmlToProductBuilder[]
     */
    public function createProductXmlToProductTypeBuilders()
    {
        return [
            $this->getMasterFactory()->createSimpleProductXmlToProductBuilder(),
            $this->getMasterFactory()->createConfigurableProductXmlToProductBuilder()
        ];
    }

    /**
     * @return SimpleProductXmlToProductBuilder
     */
    public function createSimpleProductXmlToProductBuilder()
    {
        return new SimpleProductXmlToProductBuilder();
    }

    /**
     * @return ConfigurableProductXmlToProductBuilder
     */
    public function createConfigurableProductXmlToProductBuilder()
    {
        return new ConfigurableProductXmlToProductBuilder();
    }

    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        return new ProductWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductProjector()
        );
    }

    /**
     * @param TemplateWasUpdatedDomainEvent $event
     * @return TemplateWasUpdatedDomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(TemplateWasUpdatedDomainEvent $event)
    {
        return new TemplateWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createTemplateProjectorLocator()
        );
    }
}
