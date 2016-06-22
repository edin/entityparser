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
        if ($this->type === null) {
            throw new \Exception("Type '{$this->name}' does not have a base type.");
        }

        return $this->type;
    }

    public function getIsPrimitiveType(): bool
    {
        return ($this->type == null);
    }

    public function getIsNullable(): bool
    {
        return $this->nullable;
    }

    public function getSize(): int
    {
        if ($this->size !== null) {
            return (int)$this->size;    
        }
        if ($this->type !== null) {
            return $this->type->getSize();
        }
        return 0;
    }

    public function getScale(): int
    {
        if ($this->scale !== null) {
            return (int)$this->size;    
        }
        if ($this->type !== null) {
            return $this->type->getScale();
        }
        return 0;
    }  

    function getIsEnumType(): bool
    {
        return false;
    }
    
    function getIsSetType(): bool
    {
        return false;
    }            
}