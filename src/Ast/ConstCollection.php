<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;

class ConstCollection extends Collection
{
    public function contains($name): bool
    {
        return $this->find($name)->firstOrNull() != null;
    }

    public function find($name): ConstCollection
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    
    
    public function findFirstOrNull($name) /* ?ConstantInterface */
    {
        return $this->find($name)->firstOrNull();
    }
}