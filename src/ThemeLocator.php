<?php

namespace Brera;

class ThemeLocator
{
    /**
     * @param Environment $environment
     * @return string
     */
    public function getThemeDirectoryForEnvironment(Environment $environment)
    {
        return 'theme';
    }
}
