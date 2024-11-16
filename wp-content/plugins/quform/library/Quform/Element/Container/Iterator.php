<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Container_Iterator extends RecursiveArrayIterator implements RecursiveIterator
{
    public function __construct(Quform_Element_Container $container)
    {
        parent::__construct($container->getElements());
    }

    #[ReturnTypeWillChange]
    public function hasChildren()
    {
        return $this->current() instanceof Quform_Element_Container;
    }

    #[ReturnTypeWillChange]
    public function getChildren()
    {
        return new Quform_Element_Container_Iterator($this->current());
    }
}
