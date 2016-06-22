<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\AnnotationInterface;

class ASTAnnotation implements AnnotationInterface
{
    public $name;
    public $default;
    public $attributes = [];

    public function getName(): string
    {
        return $this->name;
    }

    function getDefaultValue()
    {
        return $this->default;
    }

    function getAttributes(): array
    {
        return $this->attributes;
    }
}