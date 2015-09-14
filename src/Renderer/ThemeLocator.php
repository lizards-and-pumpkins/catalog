<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\Renderer\LayoutReader;

class ThemeLocator
{
    /**
     * @return string
     */
    public function getThemeDirectory()
    {
        return 'theme';
    }

    /**
     * @param string $layoutHandle
     * @return Layout
     */
    public function getLayoutForHandle($layoutHandle)
    {
        $layoutFile = $this->getThemeDirectory() . '/layout/' . $layoutHandle. '.xml';
        $reader = new LayoutReader();
        return $reader->loadLayoutFromXmlFile($layoutFile);
    }

    /**
     * @param string $localeCode
     * @return string
     */
    public function getLocaleDirectoryPath($localeCode)
    {
        return $this->getThemeDirectory() . '/locale/' . $localeCode;
    }
}
