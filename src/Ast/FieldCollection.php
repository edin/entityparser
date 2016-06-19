<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;

class FieldCollection extends Collection
{
    /**
     * @return boolean
     */
    public function contains($name)
    {
        return $this->find($name)->firstOrNull() != null;
    }

    /**
     * @return FieldInterface[]
     */
    public function find($name)
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    /**
     * @return FieldInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }
}