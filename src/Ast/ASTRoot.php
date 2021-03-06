<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\CodeModelInterface;

class ASTRoot implements CodeModelInterface
{
    public $constants;
    public $types;
    public $entities;
    public $enums;

    public function __construct()
    {
        $this->constants = new ConstCollection([]);
        $this->types     = new TypeCollection([]);
        $this->entities  = new EntityCollection([]);
        $this->enums     = new EnumCollection([]);       
    }

    public function getConstants(): ConstCollection
    {
        return $this->constants;
    }

    public function getTypes(): TypeCollection
    {
        return $this->types;
    }

    public function getEntities(): EntityCollection
    {
        return $this->entities;
    }

    public function getEnums(): EnumCollection
    {
        return $this->enums;
    }    

    public function getConstant($name)
    {
        return $this->constants->findFirstOrNull($name);
    }

    public function getTypeByName($name)
    {
        return $this->types->findFirstOrNull($name);
    }

    public function getEntityByName($name)
    {
        return $this->entities->findFirstOrNull($name);
    }
}