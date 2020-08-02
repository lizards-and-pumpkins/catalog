<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Exception\UnableToLocateTemplateProjectorException;

class TemplateProjectorLocator
{
    /**
     * @var Projector[]
     */
    private $projectors = [];

    public function getTemplateProjectorForCode(string $code) : Projector
    {
        if (!isset($this->projectors[$code])) {
            throw new UnableToLocateTemplateProjectorException(
                sprintf('Unable to locate projector for template code "%s".', $code)
            );
        }

        return $this->projectors[$code];
    }

    public function register(string $code, Projector $projector): void
    {
        $this->projectors[$code] = $projector;
    }

    /**
     * @return string[]
     */
    public function getRegisteredProjectorCodes() : array
    {
        return array_keys($this->projectors);
    }
}
