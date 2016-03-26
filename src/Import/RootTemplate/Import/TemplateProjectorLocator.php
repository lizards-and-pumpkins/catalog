<?php

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\InvalidTemplateProjectorCodeException;
use LizardsAndPumpkins\Import\RootTemplate\Exception\UnableToLocateTemplateProjectorException;

class TemplateProjectorLocator
{
    /**
     * @var Projector[]
     */
    private $projectors = [];

    /**
     * @param string $code
     * @return Projector
     */
    public function getTemplateProjectorForCode($code)
    {
        $this->validateProjectorCode($code);

        if (!isset($this->projectors[$code])) {
            throw new UnableToLocateTemplateProjectorException(
                sprintf('Unable to locate projector for template code "%s".', $code)
            );
        }

        return $this->projectors[$code];
    }

    /**
     * @param string $code
     * @param Projector $projector
     */
    public function register($code, Projector $projector)
    {
        $this->validateProjectorCode($code);
        $this->projectors[$code] = $projector;
    }

    /**
     * @param string $code
     */
    private function validateProjectorCode($code)
    {
        if (!is_string($code)) {
            throw new InvalidTemplateProjectorCodeException(
                sprintf('Template projectorLocator code is supposed to be a string, got %s.', gettype($code))
            );
        }
    }

    /**
     * @return string[]
     */
    public function getRegisteredProjectorCodes()
    {
        return array_keys($this->projectors);
    }
}
