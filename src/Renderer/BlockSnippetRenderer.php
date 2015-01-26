<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;

abstract class BlockSnippetRenderer implements SnippetRenderer
{
    const PARENT_CLASS = '\Brera\Renderer\Block';

    /**
     * @param string $layoutXmlFilePath
     * @param ProjectionSourceData $dataObject
     * @return string
     */
    protected function getSnippetContent($layoutXmlFilePath, ProjectionSourceData $dataObject)
    {
        $layoutReader = new LayoutReader();
        $layout = $layoutReader->loadLayoutFromXmlFile($layoutXmlFilePath);

        $outermostBlockLayout = $this->getOuterMostBlockLayout($layout);
        $outermostBlock = $this->createBlock($outermostBlockLayout, $dataObject);

        return $outermostBlock->render();
    }

    /**
     * @param Layout $layout
     * @return Layout
     * @throws SnippetShouldHaveJustOneRootBlockException
     */
    private function getOuterMostBlockLayout(Layout $layout)
    {
        $snippetPayload = $layout->getPayload();

        if (!is_array($snippetPayload) || 1 !== count($snippetPayload)) {
            throw new SnippetShouldHaveJustOneRootBlockException();
        }

        return $snippetPayload[0];
    }

    /**
     * @param Layout $layout
     * @param ProjectionSourceData $dataObject
     * @return Block
     * @throws CanNotInstantiateBlockException
     */
    private function createBlock(Layout $layout, ProjectionSourceData $dataObject)
    {
        $blockClass = $layout->getAttribute('class');

        if (is_null($blockClass)) {
            throw new CanNotInstantiateBlockException('Block class is not specified.');
        }

        if (!class_exists($blockClass)) {
            throw new CanNotInstantiateBlockException(sprintf('Class %s does not exist.'));
        }

        /** @var Block $blockInstance */
        $blockInstance = new $blockClass($layout, $dataObject);

        if (!is_subclass_of($blockInstance, $this::PARENT_CLASS)) {
            throw new CanNotInstantiateBlockException(sprintf('%s must extend %s', $blockClass, $this::PARENT_CLASS));
        }

        $children = $layout->getPayload();

        if (is_array($children)) {
            foreach ($children as $childBlockLayout) {
                $blockInstance->addChildBlock($this->createBlock($childBlockLayout, $dataObject));
            }
        }

        return $blockInstance;
    }
}
