<?php

namespace Brera\Renderer;

use Brera\Context\Context;
use Brera\ProjectionSourceData;
use Brera\ThemeLocator;

abstract class BlockRenderer
{
    /**
     * @var ThemeLocator
     */
    private $themeLocator;

    /**
     * @var ProjectionSourceData
     */
    private $dataObject;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string[]
     */
    private $missingBlockNames;
    
    /**
     * @var BlockStructure
     */
    private $blockStructure;

    /**
     * @var Block
     */
    private $outermostBlock;

    public function __construct(
        ThemeLocator $themeLocator,
        BlockStructure $blockStructure
    ) {
        $this->themeLocator = $themeLocator;
        $this->blockStructure = $blockStructure;
    }

    /**
     * @return string
     */
    abstract protected function getLayoutHandle();

    /**
     * @param ProjectionSourceData $dataObject
     * @param Context $context
     * @return string
     */
    public function render(ProjectionSourceData $dataObject, Context $context)
    {
        $this->dataObject = $dataObject;
        $this->context = $context;
        $this->missingBlockNames = [];

        $outermostBlockLayout = $this->getOuterMostBlockLayout();
        $this->outermostBlock = $this->createBlockWithChildrenRecursively($outermostBlockLayout);

        return $this->outermostBlock->render();
    }

    /**
     * @return ProjectionSourceData
     */
    final public function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return Layout
     * @throws BlockRendererMustHaveOneRootBlockException
     */
    private function getOuterMostBlockLayout()
    {
        $layout = $this->getLayout();
        $rootBlocks = $layout->getNodeChildren();

        if (!is_array($rootBlocks) || 1 !== count($rootBlocks)) {
            throw new BlockRendererMustHaveOneRootBlockException(sprintf(
                'Exactly one root block must be assigned to BlockRenderer "%s"',
                $this->getLayoutHandle()
            ));
        }

        return $rootBlocks[0];
    }

    /**
     * @param Layout $layout
     * @return Block
     */
    private function createBlockWithChildrenRecursively(Layout $layout)
    {
        $blockClass = $layout->getAttribute('class');
        $this->validateBlockClass($blockClass);

        $template = $layout->getAttribute('template');
        $name = $layout->getAttribute('name');

        /** @var Block $blockInstance */
        $blockInstance = new $blockClass($this, $template, $name, $this->dataObject);
        $this->blockStructure->addBlock($blockInstance);
        $this->addDeclaredChildBlocks($layout, $name);

        return $blockInstance;
    }

    /**
     * @param Layout $layout
     * @param string $parentName
     * @return void
     */
    private function addDeclaredChildBlocks(Layout $layout, $parentName)
    {
        if ($layout->hasChildren()) {
            /** @var Layout $childBlockLayout */
            foreach ($layout->getNodeChildren() as $childBlockLayout) {
                $childBlockInstance = $this->createBlockWithChildrenRecursively($childBlockLayout);
                $this->blockStructure->setParentBlock($parentName, $childBlockInstance);
            }
        }
    }

    /**
     * @param string $blockClass
     * @return void
     * @throws CanNotInstantiateBlockException
     */
    private function validateBlockClass($blockClass)
    {
        if (is_null($blockClass)) {
            throw new CanNotInstantiateBlockException('Block class is not specified.');
        }

        if (!class_exists($blockClass)) {
            throw new CanNotInstantiateBlockException(sprintf('Block class does not exist "%s".', $blockClass));
        }

        if (Block::class !== $blockClass && !in_array(Block::class, class_parents($blockClass))) {
            $message = sprintf('Block class "%s" must extend "%s"', $blockClass, Block::class);
            throw new CanNotInstantiateBlockException(sprintf($message));
        }
    }

    /**
     * @return Layout
     */
    private function getLayout()
    {
        return $this->themeLocator->getLayoutForHandle($this->getLayoutHandle());
    }

    /**
     * @param string $parentName
     * @param string $childName
     * @return string
     */
    public function getChildBlockOutput($parentName, $childName)
    {
        if (!$this->blockStructure->hasChildBlock($parentName, $childName)) {
            $placeholder = $this->getBlockPlaceholder($childName);
            $this->missingBlockNames[] = $childName;
            return $placeholder;
        }
        return $this->blockStructure->getBlock($childName)->render();
    }

    /**
     * @return string
     */
    public function getRootSnippetCode()
    {
        return $this->getLayoutHandle();
    }

    /**
     * @return string[]
     */
    public function getNestedSnippetCodes()
    {
        if (is_null($this->missingBlockNames)) {
            throw new MethodNotYetAvailableException(
                'The method "getNestedSnippetCodes()" can not be called before "render()" is executed'
            );
        }
        return $this->missingBlockNames;
    }

    /**
     * @param string $blockName
     * @return string
     * @todo use delegate to generate the placeholder string
     * @see \Brera\UrlKeyRequestHandler::buildPlaceholdersFromCodes()
     */
    private function getBlockPlaceholder($blockName)
    {
        return '{{snippet ' . $blockName . '}}';
    }
}
