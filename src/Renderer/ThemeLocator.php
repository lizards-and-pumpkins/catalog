<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\Utils\LocalFilesystem;

class ThemeLocator
{
    /**
     * @var string
     */
    private $themeDirectoryRelativePath;

    /**
     * @param string $themeDirectoryRelativePath
     */
    private function __construct($themeDirectoryRelativePath)
    {
        $this->themeDirectoryRelativePath = $themeDirectoryRelativePath;
    }

    /**
     * @param string $basePath
     * @return ThemeLocator
     */
    public static function fromPath($basePath)
    {
        $themeDirectoryRelativePath = (new LocalFilesystem())->getRelativePath(getcwd(), $basePath . '/theme');
        return new self($themeDirectoryRelativePath);
    }

    /**
     * @return string
     */
    public function getThemeDirectory()
    {
        return $this->themeDirectoryRelativePath;
    }

    /**
     * @param string $layoutHandle
     * @return Layout
     */
    public function getLayoutForHandle($layoutHandle)
    {
        $layoutFile = $this->themeDirectoryRelativePath . '/layout/' . $layoutHandle. '.xml';
        return (new LayoutReader())->loadLayoutFromXmlFile($layoutFile);
    }
}
