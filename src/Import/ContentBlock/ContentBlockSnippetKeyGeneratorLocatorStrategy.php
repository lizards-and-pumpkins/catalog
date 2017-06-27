<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetCode;

class ContentBlockSnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var \Closure
     */
    private $contentBlockKeyGeneratorClosure;

    public function __construct(\Closure $contentBlockKeyGeneratorClosure)
    {
        $this->contentBlockKeyGeneratorClosure = $contentBlockKeyGeneratorClosure;
    }

    public function canHandle(SnippetCode $snippetCode) : bool
    {
        return strpos((string) $snippetCode, 'content_block_') === 0;
    }

    public function getKeyGeneratorForSnippetCode(SnippetCode $snippetCode) : SnippetKeyGenerator
    {
        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->contentBlockKeyGeneratorClosure, $snippetCode);
    }
}
