<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

class ThemeLocator
{
    /**
     * @var string
     */
    private $themeDirectoryPath;

    /**
     * @var LayoutXmlFileReader
     */
    private $layoutReader;

    public function __construct(string $themeDirectoryPath, LayoutReader $layoutReader)
    {
        // TODO: Validate
        $this->themeDirectoryPath = $themeDirectoryPath . '/theme';
        $this->layoutReader       = $layoutReader;
    }

    public function getThemeDirectory(): string
    {
        return $this->themeDirectoryPath;
    }

    public function getLayoutForHandle(string $layoutHandle): Layout
    {
        $layoutFile = $this->themeDirectoryPath . '/layout/' . $layoutHandle . '.xml';

        return $this->layoutReader->loadLayout($layoutFile);
    }
}
