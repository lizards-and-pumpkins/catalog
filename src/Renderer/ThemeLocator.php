<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\Utils\LocalFilesystem;

class ThemeLocator
{
    /**
     * @var string
     */
    private $relativePathToThemeDirectory;

    /**
     * @param string $relativePathToThemeDirectory
     */
    private function __construct($relativePathToThemeDirectory)
    {
        $this->relativePathToThemeDirectory = $relativePathToThemeDirectory;
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
        return $this->relativePathToThemeDirectory;
    }

    /**
     * @param string $layoutHandle
     * @return Layout
     */
    public function getLayoutForHandle($layoutHandle)
    {
        $layoutFile = $this->relativePathToThemeDirectory . '/layout/' . $layoutHandle. '.xml';
        return (new LayoutReader())->loadLayoutFromXmlFile($layoutFile);
    }
}
