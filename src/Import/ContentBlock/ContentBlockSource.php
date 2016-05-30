<?php

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

    /**
     * @return ContentBlockId
     */
    public function getContentBlockId()
    {
        return $this->contentBlockId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function getContextData()
    {
        return $this->contextData;
    }

    /**
     * @return mixed[]
     */
    public function getKeyGeneratorParams()
    {
        return $this->keyGeneratorParams;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode([
            'id' => (string) $this->contentBlockId,
            'content' => $this->content,
            'context_data' => $this->contextData,
            'key_generator_params' => $this->keyGeneratorParams
        ]);
    }

    /**
     * @param string $json
     * @return ContentBlockSource
     */
    public static function rehydrate($json)
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
