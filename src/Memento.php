<?php


namespace Brera;

interface Memento
{
    /**
     * @param string $serializedStateString
     * @return MementoOriginator
     */
    public static function fromStringRepresentation($serializedStateString);

    /**
     * @return string
     */
    public function getStringRepresentation();
}
