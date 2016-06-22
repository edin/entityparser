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

    public function getName(): string
    {
        return $this->name;
    }

    public function getAnnotations(): AnnotationCollection
    {
        $annotations = new AnnotationCollection($this->annotations);
        if ($this->type != null) 
        {
            $baseAnnotations = $this->type->getAnnotations();
            $annotations->includeFromBase($baseAnnotations);
        }
        return $annotations;
    }

    public function getBaseType(): TypeInterface
    {
        return $this->type;
    }

    public function getIsPrimitiveType(): bool
    {
        return false;
    }

    public function getIsNullable(): bool
    {
        return false;
    }

    public function getSize(): int
    {
        if ($this->type !== null) {
            return $this->type->getSize();
        }
        return null;
    }

    public function getScale(): int
    {
        if ($this->type !== null) {
            return $this->type->getScale();
        }
        return null;
    }    

    function getIsEnumType(): bool
    {
        if ($this->type != null) {
            return $this->type->getIsEnumType();
        }
        return false;
    }

    function getIsSetType(): bool
    {
        if ($this->type != null) {
            return $this->type->getIsSetType();
        }
        return false;
    }       
}