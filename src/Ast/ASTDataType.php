<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\TypeInterface;

class ASTDataType implements TypeInterface 
{
	public $name;
	public $type;
	public $size;
    public $scale;
    public $nullable = false;
    public $annotations = [];

    function getName()
    {
        return $this->name;
    }

    function getAnnotations()
    {
        return $this->annotations;
    }

    function getBaseType()
    {
        return $this->type;
    }

    function getAttributes()
    {
        return [
            'length' => $this->size,
            'scale'  => $this->scale
        ];
    }
    
    function getIsBaseType()
    {
        return ($this->type == null);
    }

    function getIsNullable()
    {
        return $this->nullable;
    }

    function getSize()
    {
        return $this->size;
    }            
}