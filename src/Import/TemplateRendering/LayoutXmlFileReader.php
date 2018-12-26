<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\LayoutFileNotReadableException;
use LizardsAndPumpkins\Import\XPathParser;

class LayoutXmlFileReader implements LayoutReader
{
    public function loadLayout(string $layoutXmlFilePath) : Layout
    {
        if (!is_readable($layoutXmlFilePath) || is_dir($layoutXmlFilePath)) {
            throw new LayoutFileNotReadableException(sprintf(
                'The layout file "%s" is not readable.',
                $layoutXmlFilePath
            ));
        }

        $layoutXml = file_get_contents($layoutXmlFilePath);
        $layoutArray = (new XPathParser($layoutXml))->getXmlNodesArrayByXPath('/*');

        return Layout::fromArray($layoutArray);
    }
}
