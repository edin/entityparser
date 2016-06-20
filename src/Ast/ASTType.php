<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class ASTType implements TypeInterface
{
    public $name;
    public $type = null;
    public $annotations = [];

    public function getRawType()
    {
        $type = $this;
        while ($type->type != null)
        {
            $type = $type->type;
        }
        return $type->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAnnotations()
    {
        $annotations = new AnnotationCollection($this->annotations);
        if ($this->type != null) 
        {
            $baseAnnotations = $this->type->getAnnotations();
            $annotations->includeFromBase($baseAnnotations);
        }
        return $annotations;
    }

    public function getBaseType()
    {
        return $this->type;
    }

    public function getIsBaseType()
    {
        return false;
    }

    public function getIsNullable()
    {
        return false;
    }

    public function getSize()
    {
        if ($this->type !== null) {
            return $this->type->getSize();
        }
        return null;
    }

    public function getPrecision()
    {
        if ($this->type !== null) {
            return $this->type->getPrecision();
        }
        return null;
    }    

    function getEnumType()
    {
        return null;
    }
    
    function getIsSetType()
    {
        return false;
    }       
}