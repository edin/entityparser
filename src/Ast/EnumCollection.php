<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class EnumCollection extends Collection
{
    public function contains($name): bool
    {
        return $this->find($name)->firstOrNull() != null;
    }

    public function find($name): EnumCollection
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    public function findFirstOrNull($name) /* ?EnumInterface */
    {
        return $this->find($name)->firstOrNull();
    }
}