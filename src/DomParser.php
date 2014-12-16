<?php

namespace Brera\PoC;

interface DomParser
{
	/**
	 * @param string $xPathString
	 * @param \DOMElement $contextNode
	 * @param bool $getFirstNode
	 * @return \DOMElement|\DOMNodeList|null
	 */
	public function getXPathNode($xPathString, $contextNode = null, $getFirstNode = false);
}
