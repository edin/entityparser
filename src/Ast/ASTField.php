<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;
use EntityParser\Parser\Contract\TypeInterface;


class ASTField implements FieldInterface
{
    public $type;
    public $default = null;
    public $name;
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
        return new AnnotationCollection($this->annotations);
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getDefaultValue()
    {
        return $this->default;
    }
}