<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\ConstantInterface;

class ASTConst implements ConstantInterface
{
    public $name;
    public $value;
    public $annotations = [];

    public function getName()
    {
        return $this->name;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getValue()
    {
        return $this->value;
    }
}
