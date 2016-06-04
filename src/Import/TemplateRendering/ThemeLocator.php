<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

class ThemeLocator
{
    /**
     * @var string
     */
    private $themeDirectoryPath;

    /**
     * @param string $themeDirectoryPath
     */
    public function __construct($themeDirectoryPath)
    {
        // TODO: Validate
        $this->themeDirectoryPath = $themeDirectoryPath . '/theme';
    }

    /**
     * @return string
     */
    public function getThemeDirectory()
    {
        return $this->themeDirectoryPath;
    }

    /**
     * @param string $layoutHandle
     * @return Layout
     */
    public function getLayoutForHandle($layoutHandle)
    {
        $layoutFile = $this->themeDirectoryPath . '/layout/' . $layoutHandle . '.xml';
        return (new LayoutReader())->loadLayoutFromXmlFile($layoutFile);
    }
}
