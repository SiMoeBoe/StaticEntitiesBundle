<?php

namespace Simoeboe\StaticEntitiesBundle\Filter;

use FilterIterator;
use Iterator;

class KnownByNameFilter extends FilterIterator
{
    private readonly array $knownElementValues;

    public function __construct(
        Iterator $elementList,
        array $knownElements,
        private readonly string $getValueMethod
    ) {
        $this->knownElementValues = array_map(
            function ($element) use ($getValueMethod) { return $element->{$getValueMethod}(); },
            $knownElements
        );

        parent::__construct($elementList);
    }

    public function accept(): bool
    {
        $elem = $this->getInnerIterator()->current();

        return method_exists($elem, $this->getValueMethod) && in_array($elem->{$this->getValueMethod}(), $this->knownElementValues);
    }
}