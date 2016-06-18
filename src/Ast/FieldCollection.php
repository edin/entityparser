<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldCollection;

class FieldCollection implements \IteratorAggregate
{
    /**
     * @var FieldInterface[] $annotation;
     */        
    private $fields;

    public function __constuct($fields)
    {
        $this->fields = array_values($fields);
    }

    public function getIterator() 
    {
        foreach ($this->fields as $field) 
        {
            yield $field;
        }
    }

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
        $list = [];
        foreach($this->fields as $field)
        {
            if (strtolower($field->getName()) == $name)
            {
                $list[] = $field;
            }
        }
        return $list;
    }    

    /**
     * @return FieldInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }

    /**
     * @return FieldInterface | null
     */
    public function firstOrNull()
    {
        if (isset($this->fields[0])) {
            return $this->fields[0];
        }
        return null;
    }
}