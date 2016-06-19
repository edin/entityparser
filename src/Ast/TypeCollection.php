<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class TypeCollection extends Collection
{
    /**
     * @return boolean
     */
    public function contains($name)
    {
        return $this->find($name)->firstOrNull() != null;
    }

    /**
     * @return TypeInterface[]
     */
    public function find($name)
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    /**
     * @return TypeInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }
}