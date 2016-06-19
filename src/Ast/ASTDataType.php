<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class ASTDataType implements TypeInterface
{
    public $name;
    public $type = null;
    public $size;
    public $scale;
    public $nullable = false;
    public $annotations = [];

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
        return ($this->type == null);
    }

    public function getIsNullable()
    {
        return $this->nullable;
    }

    public function getSize()
    {
        if ($this->size !== null) {
            return (int)$this->size;    
        }
        if ($this->type !== null) {
            return $this->type->getSize();
        }
        return null;
    }

    public function getPrecision()
    {
        if ($this->scale !== null) {
            return (int)$this->size;    
        }
        if ($this->type !== null) {
            return $this->type->getPrecision();
        }
        return null;
    }        
}