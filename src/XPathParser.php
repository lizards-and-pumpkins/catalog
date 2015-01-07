<?php

namespace Brera;

interface XPathParser
{
	/**
	 * @param string $xPath
	 * @return array
	 */
	public function getXmlNodesArrayByXPath($xPath);

	/**
	 * @param string $xPath
	 * @return array
	 */
	public function getXmlNodesRawXmlArrayByXPath($xPath);
}
