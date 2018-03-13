<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\UnableToProcessContentBlockApiGetRequestException;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

/**
 * Class ContentBlockApiV2GetRequestHandler
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 */
class ContentBlockApiV2GetRequestHandler implements HttpRequestHandler
{

    const QUERY_PARAMETER_NAME = 'content_block_id';

    /**
     * @var ContentBlockService
     */
    private $contentBlockService;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var string|null
     */
    private $blockContent;

    /**
     * ContentBlockApiV2GetRequestHandler constructor.
     *
     * @param ContentBlockService $contentBlockService
     * @param ContextBuilder      $contextBuilder
     */
    public function __construct(ContentBlockService $contentBlockService, ContextBuilder $contextBuilder)
    {
        $this->contentBlockService = $contentBlockService;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function canProcess(HttpRequest $request): bool
    {
        if ($this->blockContent !== null) {
            return true;
        }

        if (!$request->hasQueryParameter(self::QUERY_PARAMETER_NAME)) {
            return false;
        }

        $queryParameter = trim($request->getQueryParameter(self::QUERY_PARAMETER_NAME));
        if ($queryParameter === '') {
            return false;
        }

        try {
            $context = $this->contextBuilder->createFromRequest($request);
            $this->blockContent = $this->contentBlockService->getContentBlock($queryParameter, $context);
        } catch (Exception\ContentBlockNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param HttpRequest $request
     *
     * @return HttpResponse
     */
    public function process(HttpRequest $request): HttpResponse
    {
        if (!$this->canProcess($request)) {
            throw new UnableToProcessContentBlockApiGetRequestException('canProcess must be checked before process');
        };

        return GenericHttpResponse::create($this->blockContent, [], HttpResponse::STATUS_OK);
    }
}