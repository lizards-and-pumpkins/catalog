<?php

namespace Brera\Renderer;

use Brera\Environment\Environment;
use Brera\ProjectionSourceData;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;
use Brera\ThemeLocator;

abstract class BlockSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetResultList
     */
    private $resultList;

    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var ThemeLocator
     */
    private $themeLocator;

    /**
     * @param SnippetResultList $resultList
     * @param SnippetKeyGenerator $keyGenerator
     * @param ThemeLocator $themeLocator
     */
    public function __construct(
        SnippetResultList $resultList,
        SnippetKeyGenerator $keyGenerator,
        ThemeLocator $themeLocator
    ) {
        $this->resultList = $resultList;
        $this->keyGenerator = $keyGenerator;
        $this->themeLocator = $themeLocator;
    }

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
        $outermostBlock = $this->createBlockWithChildren($outermostBlockLayout, $dataObject);

        return $outermostBlock->render();
    }

    /**
     * @param Layout $layout
     * @return Layout
     * @throws BlockSnippetRendererShouldHaveJustOneRootBlockException
     */
    private function getOuterMostBlockLayout(Layout $layout)
    {
        $snippetNodeValue = $layout->getNodeChildren();

        if (!is_array($snippetNodeValue) || 1 !== count($snippetNodeValue)) {
            throw new BlockSnippetRendererShouldHaveJustOneRootBlockException();
        }

        return $snippetNodeValue[0];
    }

    /**
     * @param Layout $layout
     * @param ProjectionSourceData $dataObject
     * @return Block
     */
    private function createBlockWithChildren(Layout $layout, ProjectionSourceData $dataObject)
    {
        $blockClass = $layout->getAttribute('class');
        $blockTemplate = $layout->getAttribute('template');

        $this->validateBlockClass($blockClass);

        /** @var Block $blockInstance */
        $blockInstance = new $blockClass($blockTemplate, $dataObject);

        $nodeChildren = $layout->getNodeChildren();

        if ($this->hasChildren($nodeChildren)) {
            /** @var Layout $childBlockLayout */
            foreach ($nodeChildren as $childBlockLayout) {
                $childBlockNameInLayout = $childBlockLayout->getAttribute('name');
                $childBlockInstance = $this->createBlockWithChildren($childBlockLayout, $dataObject);
                $blockInstance->addChildBlock($childBlockNameInLayout, $childBlockInstance);
            }
        }

        return $blockInstance;
    }

    /**
     * @param string|Layout[] $node
     * @return bool
     */
    private function hasChildren($node)
    {
        return is_array($node);
    }

    /**
     * @param string $blockClass
     * @throws CanNotInstantiateBlockException
     */
    private function validateBlockClass($blockClass)
    {
        if (is_null($blockClass)) {
            throw new CanNotInstantiateBlockException('Block class is not specified.');
        }

        if (!class_exists($blockClass)) {
            throw new CanNotInstantiateBlockException(sprintf('Class %s does not exist.', $blockClass));
        }

        if (Block::class !== $blockClass && !in_array(Block::class, class_parents($blockClass))) {
            throw new CanNotInstantiateBlockException(sprintf('%s must extend %s', $blockClass, Block::class));
        }
    }

    /**
     * @param Environment $environment
     * @return string
     */
    final protected function getPathToLayoutXmlFile(Environment $environment)
    {
        $themeDirectory = $this->themeLocator->getThemeDirectoryForEnvironment($environment);
        return $themeDirectory . '/layout/' . $this->getSnippetLayoutHandle() . '.xml';
    }

    /**
     * @return string
     */
    abstract protected function getSnippetLayoutHandle();

    /**
     * @return SnippetKeyGenerator
     */
    final protected function getKeyGenerator()
    {
        return $this->keyGenerator;
    }

    /**
     * @return SnippetResultList
     */
    final protected function getSnippetResultList()
    {
        return $this->resultList;
    }
}
