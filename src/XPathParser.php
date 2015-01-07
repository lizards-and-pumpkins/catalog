<?php

namespace Brera;

interface XPathParser
{
	/**
	 * @param string $xPathString
	 * @return array
	 */
	public function getXPathNode($xPathString);

	/**
	 * @param string $xPathString
	 * @return array
	 */
	public function getXPathNodeXml($xPathString);
}
