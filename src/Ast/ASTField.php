<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\FieldInterface;

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

    public function getName()
    {
        return $this->name;
    }

    public function getAnnotations()
    {
        return new AnnotationCollection($this->annotations);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDefaultValue()
    {
        return $this->default;
    }
}