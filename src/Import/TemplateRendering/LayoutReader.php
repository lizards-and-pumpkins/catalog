<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\LayoutFileNotReadableException;
use LizardsAndPumpkins\Import\XPathParser;

class LayoutReader
{
    public function loadLayoutFromXmlFile(string $layoutXmlFilePath) : Layout
    {
        if (!is_readable($layoutXmlFilePath) || is_dir($layoutXmlFilePath)) {
            throw new LayoutFileNotReadableException(sprintf(
                'The layout file "%s" is not readable.',
                $layoutXmlFilePath
            ));
        }

        $layoutXml = file_get_contents($layoutXmlFilePath);
        $parser = new XPathParser($layoutXml);
        $layoutArray = $parser->getXmlNodesArrayByXPath('/*');

        return Layout::fromArray($layoutArray);
    }
}
