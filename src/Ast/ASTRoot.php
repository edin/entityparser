<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\CodeModelInterface;

class ASTRoot implements CodeModelInterface
{
    public $constants = [];
    public $types     = [];
    public $entities  = [];
    

    function getConstants()
    {
        return $this->constants;
    }

    function getTypes()
    {
        return $this->types;
    }

    function getEntities()
    {
        return $this->entities;
    }

    public function getConstant($name) 
    {
        if (isset($this->constants[$name])) 
        {
            return $this->constants[$name]->value;
        }
        return null;
    }

    public function getConstantByName($name)
    {
        if (isset($this->constants[$name]))
        {
            return $this->constants[$name];
        }
        return null;
    }

    public function getTypeByName($name)
    {
        if (isset($this->types[$name]))
        {
            return $this->types[$name];
        }
        return null;
    }

    public function getEntityByName($name)
    {
        if (isset($this->entities[$name]))
        {
            return $this->entities[$name];
        }
        return null;
    }
}