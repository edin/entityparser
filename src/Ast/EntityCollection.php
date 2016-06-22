<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;

class EntityCollection extends Collection
{
    public function contains($name): bool
    {
        return $this->find($name)->firstOrNull() != null;
    }

    public function find($name): EntityCollection
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    public function findFirstOrNull($name) /* ?EntityInterface */
    {
        return $this->find($name)->firstOrNull();
    }
}