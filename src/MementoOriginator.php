<?php


namespace Brera;

interface MementoOriginator
{
    /**
     * @param Memento $memento
     * @return MementoOriginator
     */
    public static function fromState(Memento $memento);
    
    /**
     * @return Memento
     */
    public function getState();
}
