<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class TypeCollection extends Collection
{
    public function contains($name): bool
    {
        return $this->find($name)->firstOrNull() != null;
    }

    public function find($name): TypeCollection
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    public function findFirstOrNull($name) /* ?TypeInterface */
    {
        return $this->find($name)->firstOrNull();
    }
}