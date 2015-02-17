<?php

namespace Brera;

use Brera\Context\Context;

class ThemeLocator
{
    /**
     * @param Context $context
     * @return string
     */
    public function getThemeDirectoryForContext(Context $context)
    {
        return 'theme';
    }
}
