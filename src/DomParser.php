<?php

namespace Brera\PoC;

interface DomParser
{
	/**
	 * @param string $xPathString
	 * @return \DOMNodeList
	 */
	public function getXPathNode($xPathString);
}
