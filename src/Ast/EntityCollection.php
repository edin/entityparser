<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;

class EntityCollection extends Collection
{
    /**
     * @return boolean
     */
    public function contains($name)
    {
        return $this->find($name)->firstOrNull() != null;
    }

    /**
     * @return EntityInterface[]
     */
    public function find($name)
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    /**
     * @return EntityInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }
}