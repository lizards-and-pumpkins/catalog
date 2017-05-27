<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrl;
use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\Locale;
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

    /**
     * @var BaseUrlBuilder
     */
    private $assetsBaseUrlBuilder;

    public function __construct(
        ThemeLocator $themeLocator,
        BlockStructure $blockStructure,
        TranslatorRegistry $translatorRegistry,
        BaseUrlBuilder $baseUrlBuilder,
        BaseUrlBuilder $assetsBaseUrlBuilder
    ) {
        $this->themeLocator = $themeLocator;
        $this->blockStructure = $blockStructure;
        $this->translatorRegistry = $translatorRegistry;
        $this->baseUrlBuilder = $baseUrlBuilder;
        $this->assetsBaseUrlBuilder = $assetsBaseUrlBuilder;
    }

    abstract public function getLayoutHandle(): string;

    /**
     * @param mixed $dataObject
     * @param Context $context
     * @return string
     */
    public function render($dataObject, Context $context): string
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

    private function getOuterMostBlockLayout(): Layout
    {
        $layout = $this->getLayout();
        $rootBlocks = $layout->getNodeChildren();

        if (! is_array($rootBlocks) || 1 !== count($rootBlocks)) {
            throw new BlockRendererMustHaveOneRootBlockException(sprintf(
                'Exactly one root block must be assigned to BlockRenderer "%s"',
                $this->getLayoutHandle()
            ));
        }

        return $rootBlocks[0];
    }

    private function createBlockWithChildrenRecursively(Layout $layout): Block
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

    private function addDeclaredChildBlocks(Layout $layout, string $parentName)
    {
        if ($layout->hasChildren()) {
            /** @var Layout $childBlockLayout */
            foreach ($layout->getNodeChildren() as $childBlockLayout) {
                $childBlockInstance = $this->createBlockWithChildrenRecursively($childBlockLayout);
                $this->blockStructure->setParentBlock($parentName, $childBlockInstance);
            }
        }
    }

    private function validateBlockClass(string $blockClass)
    {
        if (is_null($blockClass)) {
            throw new CanNotInstantiateBlockException('Block class is not specified.');
        }

        if (! class_exists($blockClass)) {
            throw new CanNotInstantiateBlockException(sprintf('Block class does not exist "%s".', $blockClass));
        }

        if (Block::class !== $blockClass && ! in_array(Block::class, class_parents($blockClass))) {
            $message = sprintf('Block class "%s" must extend "%s"', $blockClass, Block::class);
            throw new CanNotInstantiateBlockException(sprintf($message));
        }
    }

    private function getLayout(): Layout
    {
        return $this->themeLocator->getLayoutForHandle($this->getLayoutHandle());
    }

    public function getChildBlockOutput(string $parentName, string $childName): string
    {
        if (! $this->blockStructure->hasChildBlock($parentName, $childName)) {
            $placeholder = $this->getBlockPlaceholder($childName);
            $this->missingBlockNames[] = $childName;

            return $placeholder;
        }

        return $this->blockStructure->getBlock($childName)->render();
    }

    public function getRootSnippetCode(): string
    {
        return $this->getLayoutHandle();
    }

    /**
     * @return string[]
     */
    public function getNestedSnippetCodes(): array
    {
        if (is_null($this->missingBlockNames)) {
            throw new MethodNotYetAvailableException(
                'The method "getNestedSnippetCodes()" can not be called before "render()" is executed'
            );
        }

        return $this->missingBlockNames;
    }

    public function translate(string $string): string
    {
        $locale = $this->context->getValue(Locale::CONTEXT_CODE);

        return $this->translatorRegistry->getTranslator($this->getLayoutHandle(), $locale)->translate($string);
    }

    public function getBaseUrl(): BaseUrl
    {
        return $this->baseUrlBuilder->create($this->context);
    }

    public function getAssetsBaseUrl(): BaseUrl
    {
        return $this->assetsBaseUrlBuilder->create($this->context);
    }

    public function getWebsiteCode(): string
    {
        return $this->context->getValue(Website::CONTEXT_CODE);
    }

    private function getBlockPlaceholder(string $blockName): string
    {
        // TODO use delegate to generate the placeholder string
        // @see \LizardsAndPumpkins\UrlKeyRequestHandler::buildPlaceholdersFromCodes()
        return '{{snippet ' . $blockName . '}}';
    }
}
