<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

class ThemeLocator
{
    /**
     * @var string
     */
    private $themeDirectoryPath;

    public function __construct(string $themeDirectoryPath)
    {
        // TODO: Validate
        $this->themeDirectoryPath = $themeDirectoryPath . '/theme';
    }

    public function getThemeDirectory() : string
    {
        return $this->themeDirectoryPath;
    }

    public function getLayoutForHandle(string $layoutHandle) : Layout
    {
        $layoutFile = $this->themeDirectoryPath . '/layout/' . $layoutHandle . '.xml';
        return (new LayoutReader())->loadLayoutFromXmlFile($layoutFile);
    }
}
