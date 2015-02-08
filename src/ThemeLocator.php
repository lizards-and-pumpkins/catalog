<?php

namespace Brera;

use Brera\Environment\Environment;

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
