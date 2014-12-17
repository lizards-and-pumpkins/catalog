<?php

namespace Brera\PoC;

interface DomParser
{
	/**
	 * @param string $xPathString
	 * @return \DOMElement|\DOMNodeList|null
	 */
	public function getXPathNode($xPathString);
}
