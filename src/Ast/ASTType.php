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

    function getName()
    {
        return $this->name;
    }

    function getAnnotations()
    {
        return new AnnotationCollection($this->annotations);
    }

    function getBaseType()
    {
        return $this->type;
    }

    function getIsBaseType()
    {
        return false;
    }

    function getIsNullable()
    {
        return null;
    }

    function getSize()
    {
        return $this->getBaseType()->getSize();
    }

    function getPrecision()
    {
        return $this->getBaseType()->getPrecision();
    }    
}