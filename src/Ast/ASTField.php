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

    function getName()
    {
        return $this->name;
    }

    function getAnnotations()
    {
        return $this->annotations;
    }

    function getType()
    {
        return $this->type;
    }

    function getDefaultValue()
    {
        return $this->default;
    }
}