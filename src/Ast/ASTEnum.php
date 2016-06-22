<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\EnumInterface;
use EntityParser\Parser\Contract\TypeInterface;

class ASTEnum implements EnumInterface, TypeInterface
{
    public $name;
    public $annotations = [];
    public $values;

    public function __construct()
    {
        $this->values = new EnumValueCollection();
    }

    public function getName(): string
    {
        return $this->name; 
    }
    
    public function getAnnotations(): AnnotationCollection
    {
        return new AnnotationCollection($this->annotations);
    }
    
    public function getValues(): EnumValueCollection
    {
        return $this->values;
    }

    function getBaseType(): TypeInterface
    {
        throw new \Exception("BaseType is not supported for enum types.");
    }

    function getIsPrimitiveType(): bool
    {
        return false;
    }

    function getIsNullable(): bool
    {
        return false;
    }

    function getSize(): int
    {
        return 0;
    }

    function getScale(): int
    {
        return 0;
    }

    function getIsEnumType(): bool
    {
        return true;
    }

    function getIsSetType(): bool
    {
        return false;
    }       
}