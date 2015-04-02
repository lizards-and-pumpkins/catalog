<?php


namespace Brera;

interface MementoOriginator
{
    /**
     * @param Memento $memento
     * @return MementoOriginator
     */
    public static function fromMemento(Memento $memento);
    
    /**
     * @return Memento
     */
    public function getState();
}
