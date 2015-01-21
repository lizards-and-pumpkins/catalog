<?php

namespace Brera;

use DoctrineTest\InstantiatorTestAsset\XMLReaderAsset;

class LayoutReader
{
    /**
     * @param string $layoutXmlFilePath
     * @return array
     */
    public function loadLayoutFromXmlFile($layoutXmlFilePath)
    {
        if (!is_readable($layoutXmlFilePath) || is_dir($layoutXmlFilePath)) {
            throw new LayoutFileNotReadableException();
        }

        $layoutXml = file_get_contents($layoutXmlFilePath);
        $parser = new XPathParser($layoutXml);
        $layout = $parser->getXmlNodesArrayByXPath('/*');

        return $layout;
    }
}
