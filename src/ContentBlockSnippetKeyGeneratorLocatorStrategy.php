<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetCodeCanNotBeProcessedException;

class ContentBlockSnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocatorStrategy
{
    /**
     * @var \Closure
     */
    private $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritdoc
     */
    public function canHandle($snippetCode)
    {
        return strpos($snippetCode, 'content_block_') === 0;
    }

    /**
     * @inheritdoc
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->closure, $snippetCode);
    }
}
