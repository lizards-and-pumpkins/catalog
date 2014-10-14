<?php


namespace Brera\PoC;


class ProductCreatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductCreatedDomainEvent
     */
    private $event;

    /**
     * @var ProductRenderer
     */
    private $renderer;

    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    public function __construct(
        ProductCreatedDomainEvent $event,
        ProductRenderer $renderer,
        ProductRepository $repository,
        DataPoolWriter $dataPoolWriter
    )
    {
        $this->event = $event;
        $this->renderer = $renderer;
        $this->repository = $repository;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @return null
     */
    public function process()
    {
        $productId = $this->event->getProductId();
        $product = $this->repository->findById($productId);
        $html = $this->renderer->render($product);
        $this->dataPoolWriter->setPoCProductHtml($productId, $html);
    }
} 