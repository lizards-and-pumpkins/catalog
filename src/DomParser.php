<?php

namespace Brera;

interface DomParser
{
	/**
	 * @param string $xPathString
	 * @return \DOMNodeList
	 */
	public function getXPathNode($xPathString);
}
