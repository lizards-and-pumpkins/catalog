<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockRendererMustHaveOneRootBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\CanNotInstantiateBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\MethodNotYetAvailableException;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

abstract class BlockRenderer
{
    /**
     * @var ThemeLocator
     */
    private $themeLocator;

    /**
     * @var mixed
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
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    /**
     * @var Block
     */
    private $outermostBlock;
    
    /**
     * @var BaseUrlBuilder
     */
    private $baseUrlBuilder;

    public function __construct(
        ThemeLocator $themeLocator,
        BlockStructure $blockStructure,
        TranslatorRegistry $translatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    ) {
        $this->themeLocator = $themeLocator;
        $this->blockStructure = $blockStructure;
        $this->translatorRegistry = $translatorRegistry;
        $this->baseUrlBuilder = $baseUrlBuilder;
    }

    /**
     * @return string
     */
    abstract public function getLayoutHandle();

    /**
     * @param mixed $dataObject
     * @param Context $context
     * @return string
     */
    public function render($dataObject, Context $context)
    {
        $this->dataObject = $dataObject;
        $this->context = $context;
        $this->missingBlockNames = [];

        $outermostBlockLayout = $this->getOuterMostBlockLayout();
        $this->outermostBlock = $this->createBlockWithChildrenRecursively($outermostBlockLayout);

        return $this->outermostBlock->render();
    }

    /**
     * @return mixed
     */
    final public function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return Layout
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

        $template = $this->themeLocator->getThemeDirectory() . '/' . $layout->getAttribute('template');
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
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        $locale = $this->context->getValue(ContextLocale::CODE);
        return $this->translatorRegistry->getTranslator($this->getLayoutHandle(), $locale)->translate($string);
    }

    /**
     * @return \LizardsAndPumpkins\Context\BaseUrl\BaseUrl
     */
    public function getBaseUrl()
    {
        return $this->baseUrlBuilder->create($this->context);
    }

    /**
     * @return string
     */
    public function getWebsiteCode()
    {
        return $this->context->getValue(Website::CONTEXT_CODE);
    }

    /**
     * @param string $blockName
     * @return string
     * @see \LizardsAndPumpkins\UrlKeyRequestHandler::buildPlaceholdersFromCodes()
     */
    private function getBlockPlaceholder($blockName)
    {
        // TODO use delegate to generate the placeholder string
        return '{{snippet ' . $blockName . '}}';
    }
}
