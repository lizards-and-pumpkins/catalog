<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;

class ContentBlockSource
{
    /**
     * @var ContentBlockId
     */
    private $contentBlockId;

    /**
     * @var string
     */
    private $content;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var mixed[]
     */
    private $keyGeneratorParams;

    /**
     * @param ContentBlockId $contentBlockId
     * @param string $content
     * @param Context $context
     * @param string[] $keyGeneratorParams
     */
    public function __construct(
        ContentBlockId $contentBlockId,
        $content,
        Context $context,
        array $keyGeneratorParams
    ) {
        $this->contentBlockId = $contentBlockId;
        $this->content = $content;
        $this->context = $context;
        $this->keyGeneratorParams = $keyGeneratorParams;
    }

    public function getContentBlockId() : ContentBlockId
    {
        return $this->contentBlockId;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    /**
     * @return mixed[]
     */
    public function getKeyGeneratorParams() : array
    {
        return $this->keyGeneratorParams;
    }

    public function serialize() : string
    {
        return json_encode([
            'id' => (string) $this->contentBlockId,
            'content' => $this->content,
            'context' => $this->context->jsonSerialize(),
            'key_generator_params' => $this->keyGeneratorParams
        ]);
    }

    public static function rehydrate(string $json) : ContentBlockSource
    {
        $data = json_decode($json, true);
        return new self(
            ContentBlockId::fromString($data['id']),
            $data['content'],
            SelfContainedContextBuilder::rehydrateContext($data['context']),
            $data['key_generator_params']
        );
    }
}
