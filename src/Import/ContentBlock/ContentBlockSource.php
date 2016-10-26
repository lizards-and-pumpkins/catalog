<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

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
     * @var string[]
     */
    private $contextData;

    /**
     * @var mixed[]
     */
    private $keyGeneratorParams;

    /**
     * @param ContentBlockId $contentBlockId
     * @param string $content
     * @param string[] $contextData
     * @param string[] $keyGeneratorParams
     */
    public function __construct(
        ContentBlockId $contentBlockId,
        $content,
        array $contextData,
        array $keyGeneratorParams
    ) {
        $this->contentBlockId = $contentBlockId;
        $this->content = $content;
        $this->contextData = $contextData;
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

    /**
     * @return string[]
     */
    public function getContextData() : array
    {
        return $this->contextData;
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
            'context_data' => $this->contextData,
            'key_generator_params' => $this->keyGeneratorParams
        ]);
    }

    public static function rehydrate(string $json) : ContentBlockSource
    {
        $data = json_decode($json, true);
        return new self(
            ContentBlockId::fromString($data['id']),
            $data['content'],
            $data['context_data'],
            $data['key_generator_params']
        );
    }
}
